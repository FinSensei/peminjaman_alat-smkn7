<?php

// PetugasController.php
namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;

class PetugasController extends Controller
{
   public function dashboard()
    {
        $stats = [
            'menunggu'       => Peminjaman::where('status', 'diajukan')->count(),
            'dipinjam'       => Peminjaman::where('status', 'dipinjam')->count(),
            'telat'          => Peminjaman::where('status', 'telat')->count(),
            'kembaliHariIni' => Pengembalian::whereDate('tgl_kembali', now()->toDateString())->count(),
        ];
        // ANTREAN: diajukan + telat + tenggat (dipinjam sisa <=3 hari)
        $batas = now()->addDays(3)->toDateString();
        $antrean = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where(function ($q) use ($batas) {
                $q->where('status', 'diajukan')
                  ->orWhere('status', 'telat')
                  ->orWhere(function ($q2) use ($batas) {
                      $q2->where('status', 'dipinjam')
                         ->whereDate('tgl_kembali_plan', '<=', $batas);
                  });
            })
            ->latest()->take(5)->get();
        // HITUNG HARI di controller (blade tinggal tampilkan variabel)
        $antrean->each(function ($x) {
            if ($x->status === 'telat') {
                $x->hari_telat = \Carbon\Carbon::parse($x->tgl_kembali_plan)->diffInDays(now()->toDateString());
            } elseif ($x->status === 'dipinjam') {
                $x->sisa_hari = \Carbon\Carbon::parse(now()->toDateString())->diffInDays(\Carbon\Carbon::parse($x->tgl_kembali_plan), false);
            }
        });

        return view('petugas.dashboard', compact('stats', 'antrean'));
    }

   public function indexPeminjaman(Request $request)
    {
        $search = $request->search;

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
            if ($peminjaman->status !== 'diajukan') {
                return redirect()->back()->with('error', 'Hanya peminjaman berstatus diajukan yang bisa disetujui.');
            }
            // Cek stok dulu dengan lock agar tidak race condition
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa: {$alat->stok}, butuh: {$detail->jumlah}");
                }
            }
            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                $alat->decrement('stok', $detail->jumlah);
            }

            DB::commit();
            \App\Models\Notifikasi::create([
                'user_id' => $peminjaman->user_id,
                'judul' => 'Pengajuan disetujui #' . $peminjaman->id,
                'pesan' => 'Pengajuanmu disetujui, silakan ambil alatnya.',
                'link' => route('peminjam.riwayat'),
            ]);
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tolakPeminjaman($id)
    {
        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

            // Pastikan yang bisa ditolak hanya pengajuan
            if ($peminjaman->status !== 'diajukan') {
                return redirect()->back()->with(
                    'error',
                    'Peminjaman ini tidak bisa ditolak karena statusnya sudah berubah.'
                );
            }

            $uidTolak = $peminjaman->user_id;
            $pidTolak = $peminjaman->id;
            $namaTolak = $peminjaman->user->name ?? 'Peminjam';

            // Hapus detail peminjaman terlebih dahulu
            $peminjaman->detailPinjam()->delete();

            // Hapus pengajuan peminjaman
            $peminjaman->delete();

            DB::commit();

            // NOTIF: kabari peminjam ditolak (data sudah dihapus, simpan ID saja)
            \App\Models\Notifikasi::create([
                'user_id' => $uidTolak,
                'judul' => 'Pengajuan ditolak #' . $pidTolak,
                'pesan' => 'Maaf, pengajuanmu ditolak petugas. Silakan ajukan ulang bila perlu.',
                'link' => route('peminjam.katalog'),
            ]);

            return redirect()->back()->with(
                'success',
                'Pengajuan peminjaman berhasil ditolak.'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with(
                'error',
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($peminjamanId);
            // Hitung denda otomatis sesuai selisih hari
            $tglPlan = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $hariIni = \Carbon\Carbon::now()->startOfDay();
            $hariTelat = $hariIni->greaterThan($tglPlan) ? $hariIni->diffInDays($tglPlan) : 0;
            $dendaAuto = $hariTelat * config('inventory.denda_per_hari', 5000);
            $dendaFinal = $request->filled('denda') ? (int)$request->denda : $dendaAuto;

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $dendaFinal,
                'petugas_id' => auth()->id(),
            ]);

            $statusAkhir = $hariTelat > 0 ? 'telat' : 'dikembalikan';
            $peminjaman->update(['status' => $statusAkhir]);

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengembalian berhasil dicatat dan stok dipulihkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    //pembuatan pengembalian dan cetak laporan mandiri



    // =============================
    // PEMANTAUAN PENGEMBALIAN
    // =============================
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        // Data untuk form animated: peminjaman yang masih dipinjam + search horizontal
        $searchPending = $request->input('search_pending');
        $pendingPeminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'dipinjam')
            ->when($searchPending, function($q) use ($searchPending) {
                $q->whereHas('user', function($uq) use ($searchPending) {
                    $uq->where('name', 'like', "%{$searchPending}%");
                })->orWhereHas('detailPinjam.alat', function($aq) use ($searchPending) {
                    $aq->where('nama_alat', 'like', "%{$searchPending}%");
                });
            })
            ->latest()
            ->get();

        $pengembalians = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas'
        ])
        ->when($search, function ($query, $search) {
            $query->whereHas('peminjaman.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        })
        ->orderByDesc('tgl_kembali')
        ->paginate(10)
        ->withQueryString();

        return view(
            'petugas.pengembalian.index',
            compact('pengembalians', 'pendingPeminjamans', 'search')
        );
    }


    // =============================
    // LAPORAN PEMINJAMAN
    // =============================
    public function indexLaporan(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'nullable|in:diajukan,dipinjam,dikembalikan,telat',
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian.petugas'
        ])
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tgl_pinjam', [
                $startDate,
                $endDate
            ]);
        })
        ->when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
        ->latest()
        ->get();

        return view(
            'petugas.laporan.index',
            compact(
                'peminjamans',
                'startDate',
                'endDate',
                'status'
            )
        );
    }
    
    public function cetakLaporanPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'nullable|in:diajukan,dipinjam,dikembalikan,telat',
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian.petugas'
        ])
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tgl_pinjam', [
                $startDate,
                $endDate
            ]);
        })
        ->when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
        ->latest()
        ->get();

        $pdf = Pdf::loadView(
            'petugas.laporan.pdf',
            compact(
                'peminjamans',
                'startDate',
                'endDate',
                'status'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan-peminjaman.pdf');
    }

    public function cetakLaporanExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'nullable|in:diajukan,dipinjam,dikembalikan,telat',
        ]);

        return Excel::download(
            new LaporanExport($request->start_date, $request->end_date, $request->status),
            'laporan-peminjaman.xlsx'
        );
    }

    // =============================
    // REQEDIT - AJUKAN PERBAIKAN KE ADMIN (TAMBAHAN, TIDAK UBAH METHOD LAMA)
    // =============================
    public function ajukanPerbaikan(Request $request, $id)
    {
        $request->validate([
            'catatan_perbaikan' => 'required|string|max:1000',
        ]);

        $pengembalian = Pengembalian::findOrFail($id);

        $pengembalian->update([
            'catatan_perbaikan' => $request->catatan_perbaikan,
            'butuh_perbaikan'   => true,
            'status_perbaikan'  => 'pending',
        ]);

        // catat ke log agar muncul di dashboard admin
        if (method_exists(auth()->user(), 'logAktivitas')) {
            auth()->user()->logAktivitas()->create([
                'aktivitas' => "Petugas ".auth()->user()->name." mengajukan ReqEdit pengembalian #{$pengembalian->id} (peminjaman #{$pengembalian->peminjaman_id}): {$request->catatan_perbaikan}",
            ]);
        }

        return redirect()->back()->with('success', 'ReqEdit berhasil dikirim ke Admin.');
    }

}
