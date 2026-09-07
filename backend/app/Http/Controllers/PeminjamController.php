<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    public function katalogAlat(Request $request)
    {
        $search = $request->input('search');
        $kategori_id = $request->input('kategori_id');
        $reorder = $request->input('reorder');
        $reorderMap = [];
        if ($reorder) {
            $old = Peminjaman::with('detailPinjam')->where('user_id', auth()->id())->find($reorder);
            if ($old) {
                foreach ($old->detailPinjam as $d) {
                    $reorderMap[$d->alat_id] = $d->jumlah;
                }
            }
        }

        $alats = Alat::with('kategori')
            ->tersedia()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('nama_alat', 'like', "%{$search}%")
                       ->orWhereHas('kategori', function ($k) use ($search) {
                           $k->where('nama_kategori', 'like', "%{$search}%");
                       });
                });
            })
            ->when($kategori_id, function ($q) use ($kategori_id) {
                $q->where('kategori_id', $kategori_id);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $kategoris = Kategori::all();
        $uid = auth()->id();
        $stats = [
            'total' => Peminjaman::where('user_id', $uid)->count(),
            'diajukan' => Peminjaman::where('user_id', $uid)->where('status','diajukan')->count(),
            'dipinjam' => Peminjaman::where('user_id', $uid)->where('status','dipinjam')->count(),
            'telat' => Peminjaman::where('user_id', $uid)->where('status','telat')->count(),
        ];
        return view('peminjam.katalog', compact('alats', 'search', 'kategoris', 'kategori_id', 'stats', 'reorderMap'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'alat_id' => 'required|array|min:1',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|integer|min:1',
        ]);
        DB::beginTransaction();
        try {
            foreach ($request->alat_id as $index => $alatId) {
                $jumlah = $request->jumlah[$index];
                $alat = Alat::lockForUpdate()->findOrFail($alatId);
                if ($alat->stok < $jumlah) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa: {$alat->stok}");
                }
            }
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now()->toDateString(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);
            foreach ($request->alat_id as $index => $alatId) {
                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $request->jumlah[$index],
                ]);
            }
            DB::commit();
            // NOTIF: kabari admin & petugas ada pengajuan baru
            $stafIds = \App\Models\User::whereIn('role', ['admin', 'petugas'])->pluck('id');
            foreach ($stafIds as $sid) {
                \App\Models\Notifikasi::create([
                    'user_id' => $sid,
                    'judul' => 'Pengajuan baru #' . $peminjaman->id,
                    'pesan' => (auth()->user()->name ?? 'Peminjam') . ' mengajukan peminjaman, menunggu persetujuan.',
                    'link' => route('petugas.peminjaman.index'),
                ]);
            }
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim. Menunggu persetujuan petugas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    public function riwayatPeminjaman(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $query = Peminjaman::with(['detailPinjam.alat', 'pengembalian'])
            ->where('user_id', auth()->id())
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('detailPinjam.alat', function ($k) use ($search) {
                    $k->where('nama_alat', 'like', "%{$search}%");
                });
            })
            ->latest();
        $peminjaman = $query->paginate(10)->withQueryString();
        $uid = auth()->id();
        $stats = [
            'total' => Peminjaman::where('user_id', $uid)->count(),
            'diajukan' => Peminjaman::where('user_id', $uid)->where('status','diajukan')->count(),
            'dipinjam' => Peminjaman::where('user_id', $uid)->where('status','dipinjam')->count(),
            'telat' => Peminjaman::where('user_id', $uid)->where('status','telat')->count(),
            'dikembalikan' => Peminjaman::where('user_id', $uid)->where('status','dikembalikan')->count(),
        ];
        $dendaPerHari = config('inventory.denda_per_hari', 5000);
        // NOMOR GLOBAL: ID seluruh pinjaman user, terbaru dulu (stabil di semua filter/halaman)
        $semuaId = Peminjaman::where('user_id', $uid)->latest()->pluck('id');
        return view('peminjam.riwayat', compact('peminjaman', 'status', 'search', 'stats', 'dendaPerHari', 'semuaId'));
    }

    public function batalkanPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
        if ($peminjaman->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }
        if ($peminjaman->status !== 'diajukan') {
            return redirect()->back()->with('error', 'Hanya pengajuan dengan status diajukan yang bisa dibatalkan.');
        }
        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPinjam()->delete();
            $peminjaman->delete();
        });
        return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}
