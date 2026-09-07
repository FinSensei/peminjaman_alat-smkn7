{{-- Partial: Form Pengembalian Animated untuk Petugas --}}
{{-- Cara pakai: @include('petugas.pengembalian._form-animated', ['peminjaman' => $peminjaman]) --}}

<div id="pengembalian-widget-{{ $peminjaman->id }}" class="pengembalian-widget" x-data="formKembali('{{ $peminjaman->tgl_kembali_plan }}', {{ config('inventory.denda_per_hari', 5000) }})">
    {{-- STEP 1: Pilih Status --}}
    <div class="flex gap-2 mb-3" id="status-chooser-{{ $peminjaman->id }}">
        <button type="button"
            @click="pilih('dikembalikan')"
            class="flex-1 py-2 px-3 rounded-lg border-2 border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition flex items-center justify-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Dikembalikan
        </button>
        <button type="button"
            @click="pilih('telat')"
            class="flex-1 py-2 px-3 rounded-lg border-2 border-amber-200 bg-amber-50 text-amber-700 text-xs font-bold hover:bg-amber-100 transition flex items-center justify-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Telat
        </button>
    </div>

    {{-- STEP 2: Animated Form (hidden by default) --}}
    <div id="pengembalian-form-{{ $peminjaman->id }}" x-show="buka" x-transition>
        
        <div class="flex items-center justify-between mb-3">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold" :class="status === 'dikembalikan' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" x-text="status === 'dikembalikan' ? 'Dikembalikan' : 'Telat'"></span>
            <button type="button" @click="buka = false" class="text-[11px] text-gray-400 hover:text-gray-600">✕ Batal</button>
        </div>

                    <form action="{{ route('petugas.pengembalian.proses', $peminjaman->id) }}" method="POST" class="space-y-3 bg-gray-50 border border-gray-200 rounded-lg p-3">
            @csrf
            <input type="hidden" name="status_pengembalian" :value="status">
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Tanggal Kembali</label>
                <input type="date" name="tgl_kembali" value="{{ date('Y-m-d') }}" required
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Kondisi Barang</label>
                <select name="kondisi_kembali" required
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white">
                    <option value="">-- Pilih Kondisi --</option>
                    <option value="Baik">Baik</option>
                    <option value="Rusak Ringan">Rusak Ringan</option>
                    <option value="Rusak Berat">Rusak Berat</option>
                    <option value="Hilang">Hilang</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Denda (Rp)</label>
                <input type="number" name="denda" x-model.number="denda" min="0"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white">
                <p x-show="status === 'telat'" class="text-[10px] text-gray-400 mt-1">Otomatis terisi jika telat, bisa diubah manual.</p>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2 rounded-md transition">
                Simpan Pengembalian
            </button>
        </form>

        <div class="flex items-center gap-2 my-3">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-[10px] text-gray-400">atau</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

            <form action="{{ route('petugas.pengembalian.proses', $peminjaman->id) }}?perbaikan=1" method="POST" class="space-y-3 border border-dashed border-amber-200 bg-amber-50/50 rounded-lg p-3" onsubmit="return confirm('Ajukan request perbaikan ke Admin?')">
            @csrf
            <input type="hidden" name="kondisi_kembali" value="Perlu Diperiksa">
            <input type="hidden" name="denda" value="0">
            <input type="hidden" name="butuh_perbaikan" value="1">
            <p class="text-[11px] font-semibold text-amber-800">Data ragu / salah?</p>
            <textarea name="catatan_perbaikan" rows="2" required placeholder="Tulis alasan, contoh: Kondisi salah input, denda perlu koreksi..."
                class="w-full px-2.5 py-1.5 text-xs border border-amber-200 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"></textarea>
            <button type="submit" class="w-full bg-white border border-amber-300 hover:bg-amber-100 text-amber-700 text-xs font-bold py-2 rounded-md transition">
                Ajukan Perbaikan ke Admin
            </button>
        </form>
    </div>
</div>
