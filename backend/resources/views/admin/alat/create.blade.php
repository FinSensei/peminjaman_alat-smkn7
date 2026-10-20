@extends('layouts.app')

@section('title', 'Tambah Alat – Panel Admin')
@section('header-title', 'Tambah Alat Baru')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.alat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat') }}" required placeholder="Contoh: Multimeter Digital"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('nama_alat') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kategori</label>
            <select name="kategori_id" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kategori_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Total Stok</label>
            <input type="number" name="stok" value="{{ old('stok') }}" required min="0" placeholder="Contoh: 10"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                oninput="updateStokBadikRusak(this.value)">
            @error('stok') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Stok Baik <span class="text-red-500">*</span></label>
                <input type="number" name="stok_baik" value="{{ old('stok_baik') }}" required min="0" placeholder="Contoh: 8"
                    class="w-full px-3 py-2 border border-emerald-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-emerald-50"
                    oninput="validateStokSplit()">
                @error('stok_baik') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Stok Rusak <span class="text-red-500">*</span></label>
                <input type="number" name="stok_rusak" value="{{ old('stok_rusak') }}" required min="0" placeholder="Contoh: 2"
                    class="w-full px-3 py-2 border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50"
                    oninput="validateStokSplit()">
                @error('stok_rusak') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <div id="stokError" class="mb-4 hidden text-red-500 text-sm">
            Stok Baik + Stok Rusak harus sama dengan Stok tersedia
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Status Kondisi</label>
            <select name="status_kondisi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kondisi --</option>
                <option value="Baik" {{ old('status_kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                <option value="Rusak" {{ old('status_kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
            </select>
            @error('status_kondisi') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi</label>
            <textarea name="deskripsi" rows="3" placeholder="Contoh: Digunakan untuk mengukur tegangan listrik"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Gambar Alat</label>
            <input type="file" name="gambar"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('gambar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Simpan</button>
        </div>
    </form>
</div>

<script>
    const DIPINJAM_AKTIF = 0;

    function stokTersedia(total) {
        return Math.max(0, (parseInt(total) || 0) - DIPINJAM_AKTIF);
    }

    // Dipanggil saat Total Stok berubah: sesuaikan otomatis Stok Baik / Stok Rusak
    function updateStokBadikRusak(total) {
        const baik = document.querySelector('[name="stok_baik"]');
        const rusak = document.querySelector('[name="stok_rusak"]');
        if (!baik || !rusak) return;

        const tersedia = stokTersedia(total);
        let rusakVal = parseInt(rusak.value) || 0;
        if (rusakVal > tersedia) rusakVal = tersedia;

        rusak.value = rusakVal;
        baik.value = tersedia - rusakVal;
        validateStokSplit();
    }

    function validateStokSplit() {
        const totalEl = document.querySelector('[name="stok"]');
        const baikEl = document.querySelector('[name="stok_baik"]');
        const rusakEl = document.querySelector('[name="stok_rusak"]');
        const error = document.getElementById('stokError');
        if (!totalEl || !baikEl || !rusakEl || !error) return;

        const total = parseInt(totalEl.value) || 0;
        const baik = parseInt(baikEl.value) || 0;
        const rusak = parseInt(rusakEl.value) || 0;
        const tersedia = stokTersedia(total);

        // Validasi hanya jika semua field terisi
        const allFilled = totalEl.value !== '' && baikEl.value !== '' && rusakEl.value !== '';

        if (allFilled && baik + rusak !== tersedia) {
            error.classList.remove('hidden');
        } else {
            error.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', validateStokSplit);
</script>
@endsection