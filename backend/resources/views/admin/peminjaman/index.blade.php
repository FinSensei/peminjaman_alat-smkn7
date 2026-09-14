@extends('layouts.app')

@section('title', 'Kelola Peminjaman - Panel Admin')
@section('header-title', 'Manajemen Transaksi Peminjaman')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Transaksi Peminjaman</h3>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- Form Search -->
                <form action="{{ route('admin.peminjaman.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam / status..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                        Cari
                    </button>

                    @if(request('search'))
                        <a href="{{ route('admin.peminjaman.index') }}"
                            class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                            Reset
                        </a>
                    @endif
                </form>

                <!-- Tombol Tambah -->
                <a href="{{ route('admin.peminjaman.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                    + Tambah Peminjaman
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam / Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjamans as $peminjaman)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $peminjaman->user->name ?? 'User Dihapus' }}
                            </td>

                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($peminjaman->detailPinjam as $detail)
                                        <li>
                                            <span class="font-semibold">
                                                {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                            </span>

                                            <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded">
                                                ({{ $detail->jumlah }} pcs)
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            <td class="py-3 px-4 border-b text-xs text-gray-600">
                                <span class="block">
                                    Pinjam: {{ $peminjaman->tgl_pinjam }}
                                </span>

                                <span class="block font-semibold">
                                    Rencana: {{ $peminjaman->tgl_kembali_plan }}
                                </span>
                            </td>

                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full @if($peminjaman->status == 'diajukan') bg-yellow-100 text-yellow-800 @elseif($peminjaman->status == 'dipinjam') bg-blue-100 text-blue-800 @elseif($peminjaman->status == 'dikembalikan') bg-emerald-100 text-emerald-800 @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($peminjaman->status) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 border-b">
                                <div class="flex flex-col space-y-2">
                                    @if($peminjaman->status === 'dikembalikan')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-100 border border-emerald-200 rounded-full px-3 py-1 shadow-sm w-full justify-center text-center">Sudah Dikembalikan</span>
                                    @elseif($peminjaman->status === 'telat')
                                        <button type="button" onclick="openKembali({{ $peminjaman->id }}, '{{ $peminjaman->tgl_kembali_plan }}')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold transition w-full">Proses Kembali</button>
                                    @else
                                        <form action="{{ route('admin.peminjaman.updateStatus', $peminjaman->id) }}" method="POST" class="flex items-center space-x-1 status-form" data-current="{{ $peminjaman->status }}" data-id="{{ $peminjaman->id }}" data-plan="{{ $peminjaman->tgl_kembali_plan }}">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="statusGanti(this)" class="text-xs font-semibold border border-blue-200 bg-white rounded-lg px-2 py-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                                                <option value="diajukan" {{ $peminjaman->status == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                                <option value="dipinjam" {{ $peminjaman->status == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                                <option value="dikembalikan">Dikembalikan</option>
                                            </select>
                                        </form>
                                    @endif

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.peminjaman.destroy', $peminjaman->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data peminjaman ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold transition w-full">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">
                                Belum ada data peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $peminjamans->links() }}
        </div>
    </div>
    <!-- Modal Proses Kembali (kondisi + denda) -->
    <div id="kembaliModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-5">
            <h3 class="font-bold text-gray-800 mb-1">Proses Pengembalian</h3>
            <p id="kembaliInfo" class="text-xs rounded-lg px-3 py-2 mb-3"></p>
            <form id="kembaliForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="dikembalikan">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kondisi Kembali (kosong = bagus)</label>
                <input type="text" name="kondisi_kembali" placeholder="bagus" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 mb-3 focus:outline-none">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Denda manual (kosong = otomatis)</label>
                <input type="number" name="denda" min="0" placeholder="otomatis" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                <div class="flex gap-2 mt-4">
                    <button type="button" onclick="closeKembali()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        var kembaliBase = "{{ url('admin/peminjaman') }}";
        var dendaHari = {{ config('inventory.denda_per_hari', 5000) }};
        function statusGanti(sel) {
            var form = sel.closest('form');
            if (sel.value === 'dikembalikan') { openKembali(form.dataset.id, form.dataset.plan); sel.value = form.dataset.current; return; }
            if (confirm('Ubah status ke ' + sel.value + '?')) { form.submit(); } else { sel.value = form.dataset.current; }
        }
        function openKembali(id, plan) {
            document.getElementById('kembaliForm').action = kembaliBase + '/' + id + '/status';
            var p = new Date(plan); p.setHours(0,0,0,0);
            var n = new Date(); n.setHours(0,0,0,0);
            var hari = Math.max(0, Math.round((n - p) / 86400000));
            var info = document.getElementById('kembaliInfo');
            if (hari > 0) { info.className = 'text-xs rounded-lg px-3 py-2 mb-3 bg-red-50 border border-red-200 text-red-700'; info.innerHTML = 'Telat ' + hari + ' hari — status otomatis jadi Telat, denda Rp' + (hari * dendaHari).toLocaleString('id-ID'); }
            else { info.className = 'hidden'; info.textContent = ''; }
            document.getElementById('kembaliModal').classList.remove('hidden');
        }
        function closeKembali() { document.getElementById('kembaliModal').classList.add('hidden'); }
    </script>
@endsection