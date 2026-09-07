@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')

@section('header-title', 'Kelola Pengembalian')

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


{{-- TAMBAHAN: ReqEdit Menunggu Perbaikan (sisip di atas tabel lama, tabel lama tetap di bawah) --}}
@if(isset($menungguPerbaikan) && $menungguPerbaikan->count() > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-bold text-amber-800">Menunggu Perbaikan — ReqEdit dari Petugas ({{ $menungguPerbaikan->count() }})</h3>
    </div>
    <div class="space-y-2">
        @foreach($menungguPerbaikan as $r)
            <div class="bg-white border border-amber-200 rounded-lg p-3 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-800">#{{ $r->id }} — {{ $r->peminjaman->user->name ?? '-' }} — <span class="px-2 py-0.5 rounded-full text-xs {{ $r->kondisi_kembali==='Baik'?'bg-emerald-100 text-emerald-800':($r->kondisi_kembali==='Rusak Ringan'?'bg-yellow-100 text-yellow-800':($r->kondisi_kembali==='Rusak Berat'?'bg-orange-100 text-orange-800':($r->kondisi_kembali==='Hilang'?'bg-red-100 text-red-800':'bg-emerald-100 text-emerald-800 border'))) }}">{{ $r->kondisi_kembali }}</span></div>
                    <div class="text-xs text-gray-600 mt-1">Petugas: {{ $r->petugas->name ?? '-' }} — <span class="italic">"{{ $r->catatan_perbaikan }}"</span></div>
                    <div class="text-[11px] text-gray-400">Pengembalian {{ $r->tgl_kembali?->format('d-m-Y') }} — {{ $r->updated_at->diffForHumans() }}</div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <form action="{{ route('admin.pengembalian.approve', $r->id) }}" method="POST">@csrf<button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">Setujui</button></form>
                    <form action="{{ route('admin.pengembalian.reject', $r->id) }}" method="POST">@csrf<button class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg">Tolak</button></form>
                    <a href="{{ route('admin.pengembalian.edit', $r->id) }}" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200">

    {{-- Header --}}
    <div class="p-5 border-b border-gray-200">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h2 class="text-lg font-bold text-gray-800">
                    Daftar Pengembalian
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Kelola data pengembalian alat yang telah dilakukan.
                </p>

            </div>


            {{-- Search --}}
            <form
                action="{{ route('admin.pengembalian.index') }}"
                method="GET"
                class="flex"
            >

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama peminjam..."
                    class="w-64 px-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <button
                    type="submit"
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-r-lg"
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

                    {{-- Peminjam --}}
                    <th class="py-3 px-4 border-b text-left">
                        Peminjam
                    </th>

                    {{-- Alat --}}
                    <th class="py-3 px-4 border-b text-left">
                        Alat Dipinjam
                    </th>

                    {{-- Tanggal Pinjam --}}
                    <th class="py-3 px-4 border-b text-left">
                        Tanggal Pinjam
                    </th>

                    {{-- Tanggal Kembali --}}
                    <th class="py-3 px-4 border-b text-left">
                        Tanggal Kembali
                    </th>

                    {{-- Kondisi --}}
                    <th class="py-3 px-4 border-b text-left">
                        Kondisi Kembali
                    </th>

                    {{-- Denda --}}
                    <th class="py-3 px-4 border-b text-left">
                        Denda
                    </th>

                    {{-- Petugas --}}
                    <th class="py-3 px-4 border-b text-left">
                        Petugas
                    </th>

                    {{-- Aksi --}}
                    <th class="py-3 px-4 border-b text-center">
                        Status
                    </th>
                </tr>
            </thead>


            <tbody>

                @forelse($pengembalians as $pengembalian)

                    <tr class="{{ $pengembalian->butuh_perbaikan ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-gray-50' }}">

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

                            @forelse(
                                $pengembalian->peminjaman->detailPinjam
                                as $detail
                            )

                                <div class="mb-1">

                                    <span class="font-medium text-gray-700">

                                        {{ $detail->alat->nama_alat ?? '-' }}

                                    </span>

                                    <span class="text-xs text-gray-500">

                                        × {{ $detail->jumlah }}

                                    </span>

                                </div>

                            @empty

                                <span class="text-gray-400">
                                    Tidak ada alat
                                </span>

                            @endforelse

                        </td>


                        {{-- Tanggal Pinjam --}}
                        <td class="py-4 px-4 border-b">

                            {{ $pengembalian->peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}

                        </td>


                        {{-- Tanggal Kembali --}}
                        <td class="py-4 px-4 border-b">

                            {{ $pengembalian->tgl_kembali?->format('d-m-Y') ?? '-' }}

                        </td>


                        {{-- Kondisi Kembali --}}
                        <td class="py-4 px-4 border-b">

                            @if($pengembalian->kondisi_kembali === 'Baik')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">

                                    Baik

                                </span>

                            @elseif($pengembalian->kondisi_kembali === 'Rusak Ringan')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">

                                    Rusak Ringan

                                </span>

                            @elseif($pengembalian->kondisi_kembali === 'Rusak Berat')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">

                                    Rusak Berat

                                </span>

                            @elseif($pengembalian->kondisi_kembali === 'Hilang')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">

                                    Hilang

                                </span>

                            @elseif($pengembalian->kondisi_kembali === 'Kustom' || (!in_array($pengembalian->kondisi_kembali, ['Baik','Rusak Ringan','Rusak Berat','Hilang']) && !empty($pengembalian->kondisi_kembali)))

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200" title="{{ $pengembalian->kondisi_kembali }}">

                                    {{ \Illuminate\Support\Str::limit($pengembalian->kondisi_kembali, 25) }}

                                </span>

                            @else

                                <span class="text-gray-600">

                                    {{ $pengembalian->kondisi_kembali ?? '-' }}

                                </span>

                            @endif
                            @if($pengembalian->butuh_perbaikan)
                                <div class="mt-1 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">Req: "{{ \Illuminate\Support\Str::limit($pengembalian->catatan_perbaikan, 60) }}" <span class="font-bold">PENDING</span></div>
                            @endif

                        </td>


                        {{-- Denda --}}
                        <td class="py-4 px-4 border-b">

                            @if($pengembalian->denda > 0)

                                <span class="font-semibold text-red-600">

                                    Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}

                                </span>

                            @else

                                <span class="font-semibold text-emerald-600">

                                    Rp 0

                                </span>

                            @endif

                        </td>


                        {{-- Petugas --}}
                        <td class="py-4 px-4 border-b">

                            {{ $pengembalian->petugas->name ?? '-' }}

                        </td>

                        {{-- Aksi --}}
                        <td class="py-4 px-4 border-b text-center">

                            <a
                                href="{{ route('admin.pengembalian.edit', $pengembalian->id) }}"
                                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            >
                                Edit
                            </a>
                            @if($pengembalian->butuh_perbaikan)
                                <form action="{{ route('admin.pengembalian.approve', $pengembalian->id) }}" method="POST" class="inline-block ml-1">@csrf<button class="px-2 py-1 bg-emerald-600 text-white text-[11px] rounded">OK</button></form>
                                <form action="{{ route('admin.pengembalian.reject', $pengembalian->id) }}" method="POST" class="inline-block ml-1">@csrf<button class="px-2 py-1 bg-gray-200 text-gray-700 text-[11px] rounded">X</button></form>
                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="py-10 text-center text-gray-500"
                        >

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

@endsection