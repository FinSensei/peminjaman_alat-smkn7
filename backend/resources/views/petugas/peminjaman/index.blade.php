@extends('layouts.app')

@section('title', 'Kelola Peminjaman - Petugas')

@section('header-title', 'Daftar Pengajuan Peminjaman Alat')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
        {{ session('error') }}
    </div>
@endif


<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

    <!-- HEADER -->
    <div class="px-4 py-4 border-b border-gray-200">

        <h2 class="text-base font-semibold text-gray-800 mb-3">
            Menunggu Verifikasi Persetujuan
        </h2>

        <!-- SEARCH -->
        <form
            action="{{ route('petugas.peminjaman.index') }}"
            method="GET"
            class="flex"
        >

            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Cari nama peminjam..."
                class="w-64 px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-gray-400"
            >

            <button
                type="submit"
                class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-r-lg hover:bg-gray-700"
            >
                Cari
            </button>

        </form>

    </div>


    <!-- TABLE -->
    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead>

                <tr class="bg-gray-50 border-b border-gray-200">

                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                        Peminjam
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                        Tanggal Pinjam
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                        Rencana Kembali
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                        Detail Alat
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($peminjamans as $peminjaman)

                    <tr class="border-b border-gray-200 hover:bg-gray-50">

                        <!-- PEMINJAM -->
                        <td class="px-4 py-3">

                            <span class="font-medium text-gray-800">
                                {{ $peminjaman->user->name ?? '-' }}
                            </span>

                        </td>


                        <!-- TANGGAL PINJAM -->
                        <td class="px-4 py-3 text-gray-600">

                            {{ $peminjaman->tgl_pinjam }}

                        </td>


                        <!-- RENCANA KEMBALI -->
                        <td class="px-4 py-3 text-gray-600">

                            {{ $peminjaman->tgl_kembali_plan }}

                        </td>


                        <!-- DETAIL ALAT -->
                        <td class="px-4 py-3">

                            @if($peminjaman->detailPinjam->count())

                                <ul class="list-disc pl-4 text-gray-700">

                                    @foreach($peminjaman->detailPinjam as $detail)

                                        <li>

                                            {{ $detail->alat->nama_alat ?? '-' }}

                                            (Jumlah: {{ $detail->jumlah }})

                                        </li>

                                    @endforeach

                                </ul>

                            @else

                                <span class="text-gray-400">
                                    -
                                </span>

                            @endif

                        </td>


                        <!-- AKSI -->
                        <td class="px-4 py-3 text-center">

                            @if($peminjaman->status === 'diajukan')

                                <div class="flex justify-center gap-2">

                                    <!-- SETUJUI -->
                                    <form
                                        action="{{ route('petugas.peminjaman.setujui', $peminjaman->id) }}"
                                        method="POST"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            onclick="return confirm('Setujui peminjaman alat ini?')"
                                            class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-md"
                                        >
                                            Setujui
                                        </button>

                                    </form>


                                    <!-- TOLAK -->
                                    <form
                                        action="{{ route('petugas.peminjaman.tolak', $peminjaman->id) }}"
                                        method="POST"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            onclick="return confirm('Yakin ingin menolak pengajuan peminjaman ini?')"
                                            class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-md"
                                        >
                                            Tolak
                                        </button>

                                    </form>

                                </div>


                            @elseif($peminjaman->status === 'dipinjam')

                                <!-- PENGEMBALIAN -->
                                <form
                                    action="{{ route('petugas.pengembalian.proses', $peminjaman->id) }}"
                                    method="POST"
                                    class="flex flex-col gap-1"
                                >

                                    @csrf

                                    <input
                                        type="text"
                                        name="kondisi_kembali"
                                        placeholder="Kondisi kembali"
                                        required
                                        class="px-2 py-1 text-xs border border-gray-300 rounded"
                                    >

                                    <input
                                        type="number"
                                        name="denda"
                                        placeholder="Denda"
                                        class="px-2 py-1 text-xs border border-gray-300 rounded"
                                    >

                                    <button
                                        type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-md"
                                    >
                                        Proses Pengembalian
                                    </button>

                                </form>



                            @else

                                <span class="text-xs text-gray-500">
                                    {{ ucfirst($peminjaman->status) }}
                                </span>

                            @endif

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="px-4 py-6 text-center text-gray-500"
                        >

                            Belum ada pengajuan peminjaman.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection