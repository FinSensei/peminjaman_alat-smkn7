@extends('layouts.app')

@section('title', 'Edit Pengajuan Peminjaman')
@section('header-title', 'Edit Pengajuan Peminjaman')

@section('content')
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="fixed top-20 right-6 z-50 bg-green-600 text-white pl-4 pr-3 py-3 rounded-xl shadow-lg flex gap-3 items-center max-w-sm">
    <span class="text-sm">{{ session('success') }}</span>
    <button @click="show = false" class="font-bold text-lg leading-none px-1">&times;</button>
</div>
@endif
@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition class="fixed top-20 right-6 z-50 bg-red-600 text-white pl-4 pr-3 py-3 rounded-xl shadow-lg flex gap-3 items-center max-w-sm">
    <span class="text-sm">{{ session('error') }}</span>
    <button @click="show = false" class="font-bold text-lg leading-none px-1">&times;</button>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 mb-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-4">
        <h3 class="text-lg font-bold text-gray-800">Edit Pengajuan #{{ $peminjaman->id }}</h3>
        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diajukan</span>
    </div>

    {{-- Filter Search --}}
    <form method="GET" action="{{ route('peminjam.peminjaman.edit', $peminjaman->id) }}" class="flex flex-wrap gap-2 mb-4">
        <select name="kategori_id" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Kategori</option>
            @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}" {{ ($kategori_id ?? '') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari alat..." class="border rounded-lg px-3 py-2 text-sm w-48">
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Cari</button>
        @if(!empty($search) || !empty($kategori_id))
        <a href="{{ route('peminjam.peminjaman.edit', $peminjaman->id) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Reset</a>
        @endif
    </form>

    <form action="{{ route('peminjam.peminjaman.update', $peminjaman->id) }}" method="POST" id="form-edit" x-data="{ terpilih: @js(array_map('strval', array_keys($jumlahTerpilih))), tglMin: '' }" x-init="let t = new Date(); t.setDate(t.getDate()+1); tglMin = t.toISOString().split('T')[0]">
        @csrf
        @method('PUT')

        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Tanggal Kembali <span class="text-red-500">*</span></label>
            <input type="date" name="tgl_kembali_plan" id="tgl-kembali" :min="tglMin" class="border rounded-lg px-3 py-2 text-sm w-full md:w-64" value="{{ $peminjaman->tgl_kembali_plan }}" required>
            <p class="text-xs text-gray-500 mt-1">*jika melebihi tgl kembali denda Rp{{ number_format(config('inventory.denda_per_hari'),0,',','.') }}/hari</p>
        </div>

        <div class="space-y-3">
            @forelse($alats as $alat)
                @php
                    $isTerpilih = isset($jumlahTerpilih[$alat->id]);
                    $jmlTerpilih = $jumlahTerpilih[$alat->id] ?? 1;
                @endphp
                <div data-aos="fade-up" class="border rounded-xl p-4 flex items-center gap-4 card-lift hover:bg-gray-50 {{ $isTerpilih ? 'border-blue-500 bg-blue-50/50' : '' }}">
                    <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" x-model="terpilih" @change="let inp = $el.closest('div.border').querySelector('input[type=number]'); if (inp) { if (terpilih.includes('{{ $alat->id }}')) { inp.disabled = false; inp.focus(); inp.select(); } else { inp.disabled = true; inp.value = 1; } }" class="cek-alat rounded text-blue-600 w-5 h-5 flex-shrink-0" {{ $isTerpilih ? 'checked' : '' }}>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900 text-sm">{{ $alat->nama_alat }}</div>
                        <div class="text-xs text-gray-500">{{ $alat->kategori->nama_kategori ?? '-' }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ $alat->deskripsi }}</div>
                        <div class="text-xs mt-1">
                            @if($alat->stok <= 3)
                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-bold">Hampir habis! Stok: {{ $alat->stok }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Tersedia Stok: {{ $alat->stok }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <label class="text-xs text-gray-600">Jumlah:</label>
                        <input type="number" name="jumlah[]" :disabled="!terpilih.includes('{{ $alat->id }}')" class="jumlah-input border rounded-lg px-2 py-1.5 text-sm w-20 text-center bg-white" value="{{ $jmlTerpilih }}" min="1" max="{{ $alat->stok }}" {{ $isTerpilih ? '' : 'disabled' }}>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="text-4xl mb-2">📦</div>
                    <div class="text-gray-500 text-sm">Tidak ada alat tersedia.</div>
                    <div class="text-xs text-gray-400">Coba ganti kategori atau kata kunci.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-6 flex items-center gap-3">
            <input type="hidden" name="search" value="{{ $search ?? '' }}">
            <input type="hidden" name="kategori_id" value="{{ $kategori_id ?? '' }}">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">Simpan Perubahan <span x-text="terpilih.length">{{ count($jumlahTerpilih) }}</span> Alat</button>
            <a href="{{ route('peminjam.riwayat') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Batal</a>
            <span class="text-xs text-gray-500">Jumlah bisa 1 alat atau lebih dari 1 per alat</span>
        </div>
    </form>

    <div class="mt-4">
        {{ $alats->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Ensure at least one alat is selected
    document.getElementById('form-edit').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('input[name="alat_id[]"]:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 alat!');
        }
    });
</script>
@endpush