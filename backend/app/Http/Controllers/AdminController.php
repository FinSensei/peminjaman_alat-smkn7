<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\DetailPinjam;
use App\Models\Pengembalian;

class AdminController extends Controller
{
    // Menampilkan Dashboard Admin & Log Aktivitas
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();
        // TAMBAHAN: ReqEdit menunggu (tidak ubah posisi logic lama)
        $reqEdits = Pengembalian::with(['peminjaman.user','petugas'])
            ->where('butuh_perbaikan', true)->where('status_perbaikan', 'pending')
            ->latest()->take(5)->get();
        $reqEditCount = Pengembalian::where('butuh_perbaikan', true)->where('status_perbaikan', 'pending')->count();
        $stats = [
            'alat'     => Alat::count(),
            'stokTipis'=> Alat::where('stok', '<=', 3)->count(),
            'user'     => User::count(),
            'aktif'    => Peminjaman::whereIn('status', ['diajukan','dipinjam'])->count(),
            'telat'    => Peminjaman::where('status', 'telat')->count(),
            'kembaliHariIni' => Pengembalian::whereDate('tgl_kembali', now()->toDateString())->count(),
        ];
        // GRAFIK: 6 bulan terakhir
        $grafikBulan = Peminjaman::selectRaw("DATE_FORMAT(tgl_pinjam,'%Y-%m') as bl, COUNT(*) as jml")
            ->where('tgl_pinjam', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->groupBy('bl')->orderBy('bl')->get()
            ->map(fn($r) => ['bl' => \Carbon\Carbon::createFromFormat('Y-m', $r->bl)->translatedFormat('M Y'), 'jml' => $r->jml]);
        // GRAFIK: per kategori
        $grafikKategori = DB::table('detail_pinjam')
            ->join('alat', 'alat.id', '=', 'detail_pinjam.alat_id')
            ->join('kategori', 'kategori.id', '=', 'alat.kategori_id')
            ->selectRaw('kategori.nama_kategori as kat, SUM(detail_pinjam.jumlah) as jml')
            ->groupBy('kategori.nama_kategori')->get();
        return view('admin.dashboard', compact('logs','reqEdits','reqEditCount','stats','grafikBulan','grafikKategori'));
    }

    // CRUD Alat: Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama_alat', 'like', "%{$search}%")
                      ->orWhere('status_kondisi', 'like', "%{$search}%")
                      ->orWhereHas('kategori', function ($qq) use ($search) {
                          $qq->where('nama_kategori', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }
    
    // 2. Menampilkan form tambah alat
    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    // 3. Menyimpan alat baru
    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->validated();

        // Handle Upload Gambar jika ada (pakai Storage disk public agar aman)
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('alat', 'public');
            $data['gambar'] = 'storage/' . $path;
        }

        Alat::create($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit alat
    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    // 5. Memperbarui data alat
    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->validated();

        // Handle Update Gambar jika ada file baru (Storage)
        if ($request->hasFile('gambar')) {
            if ($alat->gambar && Storage::disk('public')->exists(str_replace('storage/', '', $alat->gambar))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $alat->gambar));
            }
            $path = $request->file('gambar')->store('alat', 'public');
            $data['gambar'] = 'storage/' . $path;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    // 6. Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        // Hapus file gambar fisik jika ada (Storage)
        if ($alat->gambar) {
            $rel = str_replace('storage/', '', $alat->gambar);
            if (Storage::disk('public')->exists($rel)) {
                Storage::disk('public')->delete($rel);
            }
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // CRUD User (Manajemen User Admin, Petugas, Peminjam)
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10) // Tampilkan 10 data per halaman
            ->withQueryString(); // Memastikan parameter search tetap ada saat pindah halaman

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    // Menyimpan user baru ke database
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
            'foto_profile' => $request->hasFile('foto') ? $request->file('foto')->store('foto-profil', 'public') : null,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Memperbarui data user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->hasFile('foto')) {
            $request->validate(['foto' => 'image|mimes:jpg,jpeg,png|max:2048']);
            if ($user->foto_profile) Storage::disk('public')->delete($user->foto_profile);
            $data['foto_profile'] = $request->file('foto')->store('foto-profil', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    // Menghapus user
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->foto_profile) Storage::disk('public')->delete($user->foto_profile);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // 1. Menampilkan daftar kategori dengan pencarian + pagination
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(5) // tampilkan 5 data per halaman
            ->withQueryString(); // agar parameter search tetap ada saat pindah halaman

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    // 2. Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // 3. Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    // 5. Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        // Optional: cek apakah kategori masih dipakai oleh alat
        if ($kategori->alat()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

        // 1. Menampilkan daftar peminjaman
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    // 2. Menampilkan form tambah peminjaman
    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get(); // Atau ambil semua user jika bebas
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    // 3. Menyimpan data peminjaman baru
    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Buat transaksi utama peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan', // Status awal
            ]);

            // Simpan detail alat yang dipinjam
            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];

                $alat = Alat::findOrFail($alatId);

                // Validasi stok
                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // 4. Memperbarui status peminjaman (Misal: dari diajukan => dipinjam / selesai)
    public function updateStatusPeminjaman(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,dikembalikan,telat', // sesuaikan enum status Anda
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            // 1. LOGIKA JIKA BARANG BARU DIPINJAM (Mengurangi Stok)
            if ($statusLama != 'dipinjam' && $statusBaru == 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = $detail->alat;
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            } 
            
            // 2. LOGIKA JIKA STATUS DIUBAH MENJADI SELESAI / DIKEMBALIKAN (Mengembalikan Stok + ISI TABEL PENGEMBALIAN)
            elseif ($statusLama == 'dipinjam' && ($statusBaru == 'dikembalikan' || $statusBaru == 'dikembalikan') && !$peminjaman->pengembalian()->exists()) {
                
                // >>> PERBAIKAN: Otomatis buat data di tabel pengembalians <<<
                $tglPlan = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
                $hariTelat = \Carbon\Carbon::now()->startOfDay()->greaterThan($tglPlan) ? \Carbon\Carbon::now()->startOfDay()->diffInDays($tglPlan) : 0;
                $dendaAuto = $hariTelat * config('inventory.denda_per_hari', 5000);
                Pengembalian::create([
                    'peminjaman_id'   => $peminjaman->id,
                    'tgl_kembali'     => now(),
                    'kondisi_kembali' => 'bagus', // default jika diubah lewat status cepat
                    'denda'           => $dendaAuto,
                    'petugas_id'      => auth()->id(),
                ]);

                // Kembalikan stok barang ke inventaris
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            // Update status di tabel peminjaman
            $peminjaman->update(['status' => $statusBaru]);
            
            DB::commit();
            return redirect()->back()->with('success', 'Status peminjaman diperbarui dan riwayat pengembalian tercatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // 5. Menghapus data peminjaman
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

        if ($peminjaman->status == 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }
        }

        $peminjaman->delete();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // Menampilkan halaman Kelola Pengembalian
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        // TAMBAHAN: data ReqEdit untuk tab (posisi lama tetap)
        $menungguPerbaikan = Pengembalian::with(['peminjaman.user','peminjaman.detailPinjam.alat','petugas'])
            ->where('butuh_perbaikan', true)->where('status_perbaikan', 'pending')
            ->orderByDesc('updated_at')->get();

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
            'admin.pengembalian.index',
            compact('pengembalians', 'search', 'menungguPerbaikan', 'filter')
        );
    }

    // Menampilkan halaman edit pengembalian
    public function editPengembalian($id)
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas'
        ])->findOrFail($id);

        return view(
            'admin.pengembalian.edit',
            compact('pengembalian')
        );
    }


    // Memperbarui kondisi dan denda pengembalian
    public function updatePengembalian(Request $request, $id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string|max:255',
            'kondisi_custom' => 'nullable|string|max:255',
            'denda' => 'nullable|integer|min:0',
        ]);

        $pengembalian = Pengembalian::findOrFail($id);

        // Jika memilih Kustom, gunakan isi dari input kondisi_custom
        if ($request->kondisi_kembali === 'Kustom') {

            if (!$request->filled('kondisi_custom')) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'kondisi_custom' => 'Kondisi kustom wajib diisi.'
                    ]);
            }

            $kondisi = $request->kondisi_custom;

        } else {

            $kondisi = $request->kondisi_kembali;

        }

        $pengembalian->loadMissing('peminjaman');
        $tglPlan = \Carbon\Carbon::parse($pengembalian->peminjaman->tgl_kembali_plan)->startOfDay();
        $tglKembali = \Carbon\Carbon::parse($pengembalian->tgl_kembali)->startOfDay();
        $hariTelat = $tglKembali->greaterThan($tglPlan) ? $tglKembali->diffInDays($tglPlan) : 0;
        $dendaAuto = $hariTelat * config('inventory.denda_per_hari', 5000);
        $pengembalian->update([
            'kondisi_kembali' => $kondisi,
            'denda' => $request->filled('denda') ? (int)$request->denda : $dendaAuto,
        ]);

        return redirect()
            ->route('admin.pengembalian.index')
            ->with(
                'success',
                'Data pengembalian berhasil diperbarui.'
            );
    }

    // Menyimpan proses pengembalian
    public function storePengembalian(Request $request, $id)
    {
        $request->validate([
            'tgl_kembali' => 'required|date',
            'kondisi_kembali' => 'required|string|max:255',
            'denda' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {

            $peminjaman = Peminjaman::with([
                'detailPinjam.alat',
                'pengembalian'
            ])->findOrFail($id);

            // Pastikan status masih dipinjam
            if ($peminjaman->status !== 'dipinjam') {
                throw new \Exception(
                    'Peminjaman ini sudah tidak dapat dikembalikan.'
                );
            }

            // Pastikan belum memiliki data pengembalian
            if ($peminjaman->pengembalian) {
                throw new \Exception(
                    'Peminjaman ini sudah memiliki data pengembalian.'
                );
            }

            // Hitung denda otomatis: hariTelat x denda_per_hari (manual override bila diisi)
            $tglPlan = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $tglKembali = \Carbon\Carbon::parse($request->tgl_kembali)->startOfDay();
            $hariTelat = $tglKembali->greaterThan($tglPlan) ? $tglKembali->diffInDays($tglPlan) : 0;
            $dendaAuto = $hariTelat * config('inventory.denda_per_hari', 5000);
            // Simpan data pengembalian
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $request->tgl_kembali,
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->filled('denda') ? (int)$request->denda : $dendaAuto,
                'petugas_id' => auth()->id(),
            ]);

            // Mengembalikan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {

                $alat = $detail->alat;

                if ($alat) {
                    $alat->increment(
                        'stok',
                        $detail->jumlah
                    );
                }
            }

            // Mengubah status peminjaman
            $peminjaman->update([
                'status' => 'dikembalikan'
            ]);

            DB::commit();

            return redirect()
                ->route('admin.pengembalian.index')
                ->with(
                    'success',
                    'Pengembalian berhasil diproses dan stok alat telah dikembalikan.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // =============================
    // REQEDIT PERSETUJUAN (TAMBAHAN, TIDAK UBAH METHOD LAMA)
    // =============================
    public function approvePerbaikan($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        $pengembalian->update(['status_perbaikan' => 'disetujui', 'butuh_perbaikan' => false]);
        LogAktivitas::create(['user_id'=>auth()->id(),'aktivitas'=>"Admin menyetujui ReqEdit pengembalian #{$id}"]);
        return redirect()->back()->with('success', 'ReqEdit disetujui. Silakan Edit data pengembalian jika perlu.');
    }

    public function rejectPerbaikan($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        $pengembalian->update(['status_perbaikan' => 'ditolak', 'butuh_perbaikan' => false, 'catatan_perbaikan' => null]);
        LogAktivitas::create(['user_id'=>auth()->id(),'aktivitas'=>"Admin menolak ReqEdit pengembalian #{$id}"]);
        return redirect()->back()->with('success', 'ReqEdit ditolak.');
    }

}
