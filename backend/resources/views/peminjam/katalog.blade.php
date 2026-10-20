@extends('layouts.app')

@section('title', 'Katalog Alat')
@section('header-title', 'Katalog Alat')

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
@if(!empty($reorderMap))
<div class="bg-blue-50 border border-blue-300 rounded-xl p-4 mb-4 flex justify-between items-center">
    <div class="text-sm text-blue-800"><b>Mode Pinjam Lagi:</b> Alat dari peminjaman sebelumnya sudah dicentang. Sesuaikan jumlah lalu ajukan.</div>
    <a href="{{ route('peminjam.katalog') }}" class="text-xs bg-white border px-3 py-1 rounded-lg">Batal</a>
</div>
@endif

<div x-data="{ modalOpen: false, modalSrc: '' }" class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <!-- Image Modal for Click-to-Enlarge -->
    <div x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false" style="display: none;">
        <div class="relative max-w-4xl max-h-[90vh] w-full">
            <button @click="modalOpen = false" class="absolute -top-12 right-0 text-white/80 hover:text-white text-3xl font-bold leading-none z-10 focus:outline-none focus:ring-2 focus:ring-white rounded" aria-label="Tutup gambar">&times;</button>
            <img :src="modalSrc" alt="Gambar alat diperbesar" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
        <h3 class="text-lg font-bold text-gray-800">Katalog Alat Tersedia</h3>
        <form method="GET" action="{{ route('peminjam.katalog') }}" class="flex flex-wrap gap-2">
            <select name="kategori_id" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ ($kategori_id ?? '') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari alat..." class="border rounded-lg px-3 py-2 text-sm w-48">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Cari</button>
            @if(!empty($search) || !empty($kategori_id))
            <a href="{{ route('peminjam.katalog') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Reset</a>
            @endif
        </form>
    </div>

    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST" id="form-peminjaman" x-data="katalogForm()" @submit="if (terpilih.length === 0) { $event.preventDefault(); alert('Pilih minimal 1 alat!'); }">
        @csrf
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Tanggal Kembali <span class="text-red-500">*</span></label>
            <input type="date" name="tgl_kembali_plan" id="tgl-kembali" :min="tglMin" class="border rounded-lg px-3 py-2 text-sm w-full md:w-64" required>
            <p class="text-xs text-gray-500 mt-1">*jika melebihi tgl kembali denda Rp{{ number_format(config('inventory.denda_per_hari'),0,',','.') }}/hari</p>
        </div>

        <div class="space-y-3">
            @forelse($alats as $alat)
                @php
                    $isReorder = isset($reorderMap[$alat->id]);
                    $reorderJml = $reorderMap[$alat->id] ?? 1;
                @endphp
                <div data-aos="fade-up" class="border rounded-xl p-4 flex flex-wrap items-center gap-4 card-lift hover:bg-gray-50 {{ $isReorder ? 'border-blue-500 bg-blue-50/50' : '' }}">
                    <div class="flex-shrink-0 w-20 h-20 relative">
                        @if($alat->gambar_url)
                            <img src="{{ $alat->gambar_url }}" alt="{{ $alat->nama_alat }}"
                                class="w-full h-full object-cover rounded-lg border cursor-pointer hover:opacity-80 transition-opacity"
                                @click="modalSrc = '{{ $alat->gambar_url }}'; modalOpen = true"
                                title="Klik untuk memperbesar">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg border text-gray-400 text-xs">Tidak ada gambar</div>
                        @endif
                    </div>
                    <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" x-model="terpilih" @change="handleCheckboxChange('{{ $alat->id }}', $event.target.checked)" class="cek-alat rounded text-blue-600 w-5 h-5 flex-shrink-0 mt-2" :checked="isChecked('{{ $alat->id }}')">
                    <div class="flex-1 min-w-[150px]">
                        <div class="font-semibold text-gray-900 text-sm">{{ $alat->nama_alat }}</div>
                        <div class="text-xs text-gray-500">{{ $alat->kategori->nama_kategori ?? '-' }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ $alat->deskripsi }}</div>
                        <div class="text-xs mt-1">
                            @if($alat->stok_baik <= 3)
                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-bold">Hampir habis! Stok: {{ $alat->stok_baik }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Tersedia Stok: {{ $alat->stok_baik }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <label class="text-xs text-gray-600 whitespace-nowrap">Jumlah:</label>
                        <input type="number" name="jumlah[]" :disabled="!isChecked('{{ $alat->id }}')" class="jumlah-input border rounded-lg px-2 py-1.5 text-sm w-16 text-center bg-white" :value="getJumlah('{{ $alat->id }}')" min="1" max="{{ $alat->stok_baik }}" @input="updateJumlah('{{ $alat->id }}', $event.target.value)">
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
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">Ajukan <span x-text="terpilih.length">{{ count($reorderMap) }}</span> Alat</button>
            <span class="text-xs text-gray-500">Jumlah bisa 1 alat atau bisa lebih dari 1 per alat</span>
        </div>
    </form>

    <div class="mt-4">
        {{ $alats->links() }}
    </div>
</div>

<script>
function katalogForm() {
    const STORAGE_KEY = 'katalog_terpilih';
    const JUMLAH_KEY = 'katalog_jumlah';

    // Load from localStorage
    function loadTerpilih() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            return stored ? JSON.parse(stored) : [];
        } catch (e) { return []; }
    }

    function loadJumlah() {
        try {
            const stored = localStorage.getItem(JUMLAH_KEY);
            return stored ? JSON.parse(stored) : {};
        } catch (e) { return {}; }
    }

    function saveTerpilih(arr) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
    }

    function saveJumlah(obj) {
        localStorage.setItem(JUMLAH_KEY, JSON.stringify(obj));
    }

    // Initial data from reorder
    const reorderTerpilih = @js(array_map('strval', array_keys($reorderMap ?? [])));
    const reorderJumlah = @js($reorderMap ?? []);

    return {
        terpilih: [],
        jumlah: {},
        tglMin: '',
        modalOpen: false,
        modalSrc: '',

        init() {
            // Merge localStorage with reorder data
            const storedTerpilih = loadTerpilih();
            const storedJumlah = loadJumlah();

            // Start with reorder data
            this.terpilih = [...reorderTerpilih];
            this.jumlah = { ...reorderJumlah };

            // Add any additional from localStorage (for pagination persistence)
            storedTerpilih.forEach(id => {
                if (!this.terpilih.includes(id)) this.terpilih.push(id);
            });
            Object.keys(storedJumlah).forEach(id => {
                if (!this.jumlah[id]) this.jumlah[id] = storedJumlah[id];
            });

            // Save merged state
            this.persist();

            // Date min
            let t = new Date();
            t.setDate(t.getDate() + 1);
            this.tglMin = t.toISOString().split('T')[0];
        },

        persist() {
            saveTerpilih(this.terpilih);
            saveJumlah(this.jumlah);
        },

        isChecked(id) {
            return this.terpilih.includes(String(id));
        },

        getJumlah(id) {
            return this.jumlah[String(id)] || 1;
        },

        handleCheckboxChange(id, checked) {
            const sid = String(id);
            if (checked) {
                if (!this.terpilih.includes(sid)) this.terpilih.push(sid);
                if (!this.jumlah[sid]) this.jumlah[sid] = 1;
            } else {
                this.terpilih = this.terpilih.filter(x => x !== sid);
                delete this.jumlah[sid];
            }
            this.persist();
        },

        updateJumlah(id, value) {
            const sid = String(id);
            const num = parseInt(value, 10);
            if (!isNaN(num) && num > 0) {
                this.jumlah[sid] = num;
                this.persist();
            }
        },

        clearStorage() {
            localStorage.removeItem(STORAGE_KEY);
            localStorage.removeItem(JUMLAH_KEY);
            this.terpilih = [];
            this.jumlah = {};
        }
    };
}
</script>
@endsection


