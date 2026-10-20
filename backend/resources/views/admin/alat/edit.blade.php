@extends('layouts.app')

@section('title', 'Edit Alat – Panel Admin')
@section('header-title', 'Edit Data Alat')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.alat.update', $alat->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat', $alat->nama_alat) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kategori</label>
            <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ old('kategori_id', $alat->kategori_id) == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Total Stok</label>
            <input type="number" name="stok" value="{{ old('stok', $alat->stok) }}" min="0" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                oninput="updateStokBadikRusak(this.value)">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Stok Baik <span class="text-red-500">*</span></label>
                <input type="number" name="stok_baik" value="{{ old('stok_baik', $alat->stok_baik) }}" required min="0" placeholder="Contoh: 8"
                    class="w-full px-3 py-2 border border-emerald-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-emerald-50"
                    oninput="validateStokSplit()">
                @error('stok_baik') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Stok Rusak <span class="text-red-500">*</span></label>
                <input type="number" name="stok_rusak" value="{{ old('stok_rusak', $alat->stok_rusak) }}" required min="0" placeholder="Contoh: 2"
                    class="w-full px-3 py-2 border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50"
                    oninput="validateStokSplit()">
                @error('stok_rusak') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        @php $stokTersedia = max(0, (int) $alat->stok - (int) $dipinjamAktif); @endphp
        <div id="stokError" class="mb-4 hidden text-red-500 text-sm">
            Stok Baik + Stok Rusak harus sama dengan Stok tersedia ({{ $stokTersedia }})
        </div>
        @if($dipinjamAktif > 0)
            <div class="mb-4 text-xs text-blue-800 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                {{ $dipinjamAktif }} unit sedang dipinjam, sehingga stok tersedia di gudang hanya {{ $stokTersedia }} unit.
            </div>
        @endif

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Status Kondisi</label>
            <select name="status_kondisi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Baik" {{ old('status_kondition', $alat->status_kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                <option value="Rusak" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
            </select>
            @error('status_kondisi') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $alat->deskripsi) }}</textarea>
            @error('deskripsi') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Gambar Alat <span class="text-xs text-gray-400 font-normal">(Biarkan kosong jika tidak ingin mengubah gambar)</span>
            </label>
            @if($alat->gambar_url)
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ $alat->gambar_url }}" alt="Preview {{ $alat->nama_alat }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                    <span class="text-xs text-gray-500">Gambar saat ini</span>
                </div>
            @elseif($alat->gambar)
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    File gambar ({{ $alat->gambar }}) tidak ditemukan di server. Silakan unggah gambar baru.
                </div>
            @endif
            <input type="file" name="gambar" accept="image/*"
                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @error('gambar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Perbarui</button>
        </div>
    </form>
</div>

<script>
    const DIPINJAM_AKTIF = {{ (int) ($dipinjamAktif ?? 0) }};

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