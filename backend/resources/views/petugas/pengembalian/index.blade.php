@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian - Petugas')
@section('header-title', 'Pemantauan Pengembalian')

@section('content')

    {{-- Alert success --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Alert error --}}
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================ --}}
    {{--  PROSES PENGEMBALIAN - HORIZONTAL SCROLL     --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">

        {{-- Header + Search pending --}}
        <div class="p-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">
                    Proses Pengembalian
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Geser ke kanan. Klik
                    <span class="font-semibold text-emerald-600">Dikembalikan</span>
                    /
                    <span class="font-semibold text-amber-600">Telat</span>
                    untuk buka form detail
                </p>
            </div>

            <form
                action="{{ route('petugas.pengembalian.index') }}"
                method="GET"
                class="flex shrink-0"
            >
                <input
                    type="text"
                    name="search_pending"
                    value="{{ request('search_pending') }}"
                    placeholder="Cari yang masih meminjam..."
                    class="w-56 px-3 py-2 border border-gray-300 rounded-l-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"
                >
                <button
                    type="submit"
                    class="px-4 py-2 bg-gray-800 text-white text-sm rounded-r-lg hover:bg-gray-700"
                >
                    Cari
                </button>

                @if(request('search_pending'))
                    <a
                        href="{{ route('petugas.pengembalian.index') }}"
                        class="ml-2 px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- List card horizontal --}}
        @if(isset($pendingPeminjamans) && $pendingPeminjamans->count() > 0)
            <div class="p-5">
                <div
                    class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory"
                    style="scrollbar-width: thin;"
                >
                    @foreach($pendingPeminjamans as $peminjaman)
                        <div class="min-w-[300px] max-w-[320px] shrink-0 snap-start border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col">

                            {{-- Card header --}}
                            <div class="flex items-start justify-between mb-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-gray-800 truncate">
                                        {{ $peminjaman->user->name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $peminjaman->tgl_pinjam?->format('d-m-Y') }} -> {{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') }}<span id="tgl-plan-{{ $peminjaman->id }}" data-plan="{{ $peminjaman->tgl_kembali_plan }}" class="hidden"></span>
                                    </div>
                                </div>
                                <span class="ml-2 shrink-0 px-2 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                    dipinjam
                                </span>
                            </div>

                            {{-- List alat --}}
                            <div class="mb-3 flex-1">
                                <div class="text-[11px] font-semibold text-gray-600 mb-1">
                                    Alat:
                                </div>
                                @foreach($peminjaman->detailPinjam as $d)
                                    <div class="text-xs text-gray-700 truncate">
                                        • {{ $d->alat->nama_alat ?? '-' }} x {{ $d->jumlah }}
                                    </div>
                                @endforeach
                            </div>

                            {{-- Tombol aksi --}}
                            <div class="flex gap-2 mt-auto">
                                <button
                                    type="button"
                                    data-role="open-single-form"
                                    data-id="{{ $peminjaman->id }}"
                                    data-status="dikembalikan"
                                    data-name="{{ addslashes($peminjaman->user->name ?? '-') }}"
                                    class="flex-1 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition"
                                >
                                    Dikembalikan
                                </button>
                                <button
                                    type="button"
                                    data-role="open-single-form"
                                    data-id="{{ $peminjaman->id }}"
                                    data-status="telat"
                                    data-name="{{ addslashes($peminjaman->user->name ?? '-') }}"
                                    class="flex-1 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition"
                                >
                                    Telat
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-[11px] text-gray-400 mt-2">
                    Geser ke kanan ({{ $pendingPeminjamans->count() }} antrian)
                </p>
            </div>
        @else
            <div class="p-8 text-center text-sm text-gray-500">
                Tidak ada peminjaman yang perlu diproses.
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{--  MODAL SINGLE FORM - PROSES PENGEMBALIAN     --}}
    {{-- ============================================ --}}
    <div
        id="singleFormOverlay"
        class="fixed inset-0 bg-black/50 hidden z-40"
        onclick="closeSingleForm()"
    ></div>

    <div
        id="singleFormModal"
        class="fixed inset-0 hidden z-50 flex items-center justify-center p-4"
    >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">

            {{-- Modal header --}}
            <div class="p-5 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white rounded-t-xl">
                <div>
                    <h3 class="font-bold text-gray-800" id="modalTitle">
                        Proses Pengembalian
                    </h3>
                    <p class="text-xs text-gray-500" id="modalSubtitle">
                        -
                    </p>
                </div>
                <button
                    type="button"
                    onclick="closeSingleForm()"
                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600"
                >
                    X
                </button>
            </div>

            {{-- Modal body --}}
            <div class="p-5">
                <div class="mb-4">
                    <span
                        id="modalBadge"
                        class="inline-flex px-3 py-1 rounded-full text-xs font-bold"
                    ></span>
                </div>

                <form
                    id="modalForm"
                    action=""
                    method="POST"
                    class="space-y-4"
                >
                    @csrf
                    <input type="hidden" name="status_pengembalian" id="modalStatus" value="">

                    {{-- Tanggal kembali --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Tanggal Kembali
                        </label>
                        <input
                            type="date"
                            name="tgl_kembali"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                        >
                    </div>

                    {{-- Kondisi --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Kondisi Barang
                        </label>
                        <select
                            name="kondisi_kembali"
                            id="modalKondisi"
                            required
                            onchange="toggleKustom(this.value)"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white"
                        >
                            <option value="">-- Pilih Kondisi --</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Hilang">Hilang</option>
                            <option value="Kustom">Kustom</option>
                        </select>
                    <input
                        type="text"
                        id="modalKustomInput"
                        placeholder="Ketik kondisi kustom..."
                        class="w-full mt-2 px-3 py-2 text-sm border border-emerald-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 hidden"
                    >
                    </div>

                    {{-- Denda --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Denda (Rp)
                        </label>
                        <input
                            type="number"
                            name="denda"
                            id="modalDenda"
                            value="0"
                            min="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                        >
                        <p
                            id="modalHint"
                            class="text-[11px] text-gray-400 mt-1 hidden"
                        >
                            Otomatis terisi jika telat, bisa diubah manual.
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition"
                    >
                        Simpan Pengembalian
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{--  DAFTAR PENGEMBALIAN - RIWAYAT               --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        {{-- Header --}}
        <div class="p-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Daftar Pengembalian
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Pemantauan pengembalian alat yang telah diproses.
                    </p>
                </div>

                <form
                    action="{{ route('petugas.pengembalian.index') }}"
                    method="GET"
                    class="flex"
                >
                    <input
                        type="text"
                        name="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Cari nama peminjam..."
                        class="w-64 px-3 py-2 border border-gray-300 rounded-l-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"
                    >
                    <button
                        type="submit"
                        class="px-4 py-2 bg-gray-800 text-white text-sm rounded-r-lg"
                    >
                        Cari
                    </button>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 border-b text-left">Peminjam</th>
                        <th class="py-3 px-4 border-b text-left">Alat</th>
                        <th class="py-3 px-4 border-b text-left">Tanggal Pinjam</th>
                        <th class="py-3 px-4 border-b text-left">Tanggal Kembali</th>
                        <th class="py-3 px-4 border-b text-left">Kondisi</th>
                        <th class="py-3 px-4 border-b text-left">Denda</th>
                        <th class="py-3 px-4 border-b text-left">Petugas</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pengembalians as $pengembalian)
                        <tr class="hover:bg-gray-50">

                            {{-- Peminjam --}}
                            <td class="py-4 px-4 border-b">
                                <div class="font-semibold text-gray-800">
                                    {{ $pengembalian->peminjaman->user->name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $pengembalian->peminjaman->user->email ?? '-' }}
                                </div>
                            </td>

                            {{-- Alat --}}
                            <td class="py-4 px-4 border-b">
                                @forelse($pengembalian->peminjaman->detailPinjam as $detail)
                                    <div class="mb-1">
                                        <span class="font-medium">
                                            {{ $detail->alat->nama_alat ?? '-' }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            x {{ $detail->jumlah }}
                                        </span>
                                    </div>
                                @empty
                                    <span class="text-gray-400">Tidak ada alat</span>
                                @endforelse
                            </td>

                            {{-- Tgl pinjam --}}
                            <td class="py-4 px-4 border-b">
                                {{ $pengembalian->peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}
                            </td>

                            {{-- Tgl kembali --}}
                            <td class="py-4 px-4 border-b">
                                {{ $pengembalian->tgl_kembali?->format('d-m-Y') ?? '-' }}
                            </td>

                            {{-- Kondisi --}}
                            <td class="py-4 px-4 border-b">
                            @if($pengembalian->kondisi_kembali === 'Baik')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Baik</span>
                            @elseif($pengembalian->kondisi_kembali === 'Rusak Ringan')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Rusak Ringan</span>
                            @elseif($pengembalian->kondisi_kembali === 'Rusak Berat')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Rusak Berat</span>
                            @elseif($pengembalian->kondisi_kembali === 'Hilang')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Hilang</span>
                            @elseif($pengembalian->kondisi_kembali === 'Kustom' || (!in_array($pengembalian->kondisi_kembali, ['Baik','Rusak Ringan','Rusak Berat','Hilang']) && !empty($pengembalian->kondisi_kembali)))
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200" title="{{ $pengembalian->kondisi_kembali }}">{{ \Illuminate\Support\Str::limit($pengembalian->kondisi_kembali, 25) }}</span>
                            @else
                                {{ $pengembalian->kondisi_kembali ?? '-' }}
                            @endif
                            </td>

                            {{-- Denda --}}
                            <td class="py-4 px-4 border-b">
                                @if($pengembalian->denda > 0)
                                    <span class="font-semibold text-red-600">
                                        Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="font-semibold text-emerald-600">Rp 0</span>
                                @endif
                            </td>

                            {{-- Petugas --}}
                            <td class="py-4 px-4 border-b">
                                {{ $pengembalian->petugas->name ?? '-' }}
                            </td>

                            {{-- Aksi ReqEdit --}}
                            <td class="py-4 px-4 border-b text-center">
                                <button
                                    type="button"
                                    data-role="open-req-edit"
                                    data-id="{{ $pengembalian->id }}"
                                    data-kondisi="{{ addslashes($pengembalian->kondisi_kembali ?? '-') }}"
                                    class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 text-xs font-bold rounded-md border border-amber-200 transition"
                                >
                                    ReqEdit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-500">
                                Belum ada data pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4">
            {{ $pengembalians->links() }}
        </div>
    </div>

    {{-- ============================================ --}}
    {{--  MODAL REQEDIT - AJUKAN PERBAIKAN            --}}
    {{-- ============================================ --}}
    <div
        id="reqEditOverlay"
        class="fixed inset-0 bg-black/50 hidden z-40"
        onclick="closeReqEdit()"
    ></div>

    <div
        id="reqEditModal"
        class="fixed inset-0 hidden z-50 flex items-center justify-center p-4"
    >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">
                    Ajukan Perbaikan ke Admin
                </h3>
                <button
                    type="button"
                    onclick="closeReqEdit()"
                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center"
                >
                    X
                </button>
            </div>

            <form
                id="reqEditForm"
                action=""
                method="POST"
                class="p-5 space-y-4"
            >
                @csrf
                <p class="text-xs text-gray-500">
                    Kondisi saat ini:
                    <span id="reqEditKondisi" class="font-semibold text-gray-800"></span>
                </p>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Catatan Perbaikan
                    </label>
                    <textarea
                        name="catatan_perbaikan"
                        rows="3"
                        required
                        placeholder="Tulis alasan, contoh: Denda salah..."
                        class="w-full px-3 py-2 text-sm border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400"
                    ></textarea>
                </div>

                <input type="hidden" name="butuh_perbaikan" value="1">

                <button
                    type="submit"
                    class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-lg"
                >
                    Kirim ReqEdit ke Admin
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('modalForm').addEventListener('submit', function(e){
            const sel = document.getElementById('modalKondisi');
            const inp = document.getElementById('modalKustomInput');
            if(sel.value === 'Kustom'){
                if(!inp.value.trim()){
                    e.preventDefault();
                    inp.focus();
                    alert('Isi kondisi kustom terlebih dahulu');
                    return false;
                }
                // create hidden input with custom value to override select
                let hidden = document.getElementById('kustomHidden');
                if(!hidden){
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'kondisi_kembali';
                    hidden.id = 'kustomHidden';
                    this.appendChild(hidden);
                }
                hidden.value = inp.value.trim();
                // disable original select so its value not sent duplicated
                sel.disabled = true;
            }
        });

        function openSingleForm(id, status, name){
            document.getElementById('modalForm').action = '/petugas/pengembalian/' + id;
            document.getElementById('modalStatus').value = status;
            document.getElementById('modalSubtitle').textContent = name + ' - ID #' + id;

            const badge = document.getElementById('modalBadge');
            const denda = document.getElementById('modalDenda');
            const hint  = document.getElementById('modalHint');
            const title = document.getElementById('modalTitle');

            if(status === 'dikembalikan'){
                title.textContent = 'Proses Dikembalikan';
                badge.textContent = 'Dikembalikan';
                badge.className = 'inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700';
                denda.value = 0;
                hint.classList.add('hidden');
            } else {
                title.textContent = 'Proses Telat';
                badge.textContent = 'Telat';
                badge.className = 'inline-flex px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700';
                const planVal2 = document.getElementById('tgl-plan-' + id)?.dataset.plan;
                let ht2 = 1; if(planVal2){ const p=new Date(planVal2); const t=new Date(); t.setHours(0,0,0,0); p.setHours(0,0,0,0); ht2=Math.max(1, Math.ceil((t-p)/86400000)); }
                denda.value = String(ht2 * {{ config('inventory.denda_per_hari', 5000) }});
                hint.classList.remove('hidden');
            }

            // reset kustom state on open
            const selOpen = document.getElementById('modalKondisi');
            const inpOpen = document.getElementById('modalKustomInput');
            const hiddenOld = document.getElementById('kustomHidden');
            if(hiddenOld) hiddenOld.remove();
            if(selOpen){ selOpen.disabled = false; selOpen.required = true; }
            if(inpOpen){ inpOpen.classList.add('hidden'); inpOpen.required = false; inpOpen.value = ''; }
            document.getElementById('singleFormOverlay').classList.remove('hidden');
            document.getElementById('singleFormModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function toggleKustom(val){
            const inp = document.getElementById('modalKustomInput');
            const sel = document.getElementById('modalKondisi');
            if(val === 'Kustom'){
                inp.classList.remove('hidden');
                inp.required = true;
                inp.focus();
            } else {
                inp.classList.add('hidden');
                inp.required = false;
                inp.value = '';
            }
        }

        function closeSingleForm(){
            document.getElementById('singleFormOverlay').classList.add('hidden');
            document.getElementById('singleFormModal').classList.add('hidden');
            document.body.style.overflow = '';
            // reset kustom
            const sel = document.getElementById('modalKondisi');
            const inp = document.getElementById('modalKustomInput');
            if(sel){ sel.value = ''; sel.disabled = false; sel.required = true; }
            if(inp){ inp.value=''; inp.classList.add('hidden'); inp.required=false; }
            const h = document.getElementById('kustomHidden');
            if(h) h.remove();
        }

        function openReqEdit(id, kondisi){
            document.getElementById('reqEditKondisi').textContent = kondisi;
            document.getElementById('reqEditForm').action = '/petugas/pengembalian/' + id + '/reqedit';
            document.getElementById('reqEditOverlay').classList.remove('hidden');
            document.getElementById('reqEditModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeReqEdit(){
            document.getElementById('reqEditOverlay').classList.add('hidden');
            document.getElementById('reqEditModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (event) {
            const singleTrigger = event.target.closest('[data-role="open-single-form"]');
            if (singleTrigger) {
                openSingleForm(
                    singleTrigger.dataset.id,
                    singleTrigger.dataset.status,
                    singleTrigger.dataset.name || '-'
                );
            }

            const reqEditTrigger = event.target.closest('[data-role="open-req-edit"]');
            if (reqEditTrigger) {
                openReqEdit(reqEditTrigger.dataset.id, reqEditTrigger.dataset.kondisi || '-');
            }
        });

        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape'){
                closeSingleForm();
                closeReqEdit();
            }
        });
    </script>
    @endpush

@endsection
