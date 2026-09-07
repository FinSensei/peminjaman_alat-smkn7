@extends('layouts.app')

@section('title', 'Cetak Laporan - Petugas')

@section('header-title', 'Cetak Laporan')

@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-200">

    {{-- FILTER --}}
    <div class="p-5 border-b border-gray-200">

        <h2 class="text-lg font-bold text-gray-800 mb-4">
            Laporan Transaksi Peminjaman
        </h2>

        <form
            action="{{ route('petugas.laporan.index') }}"
            method="GET"
            class="flex flex-wrap items-end gap-4"
        >

            {{-- TANGGAL MULAI --}}
            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Mulai
                </label>

                <input
                    type="date"
                    name="start_date"
                    value="{{ $startDate }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >

            </div>


            {{-- TANGGAL AKHIR --}}
            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Akhir
                </label>

                <input
                    type="date"
                    name="end_date"
                    value="{{ $endDate }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >

            </div>


            {{-- STATUS --}}
            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                </label>

                <select
                    name="status"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >

                    <option value="">
                        Semua Status
                    </option>

                    <option value="diajukan"
                        {{ $status === 'diajukan' ? 'selected' : '' }}>
                        Diajukan
                    </option>

                    <option value="dipinjam"
                        {{ $status === 'dipinjam' ? 'selected' : '' }}>
                        Dipinjam
                    </option>

                    <option value="dikembalikan"
                        {{ $status === 'dikembalikan' ? 'selected' : '' }}>
                        Dikembalikan
                    </option>

                    <option value="telat"
                        {{ $status === 'telat' ? 'selected' : '' }}>
                        Telat
                    </option>

                </select>

            </div>


            {{-- CARI --}}
            <button
                type="submit"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-semibold"
            >
                Tampilkan
            </button>


            {{-- CETAK --}}
            <a
                href="{{ route('petugas.laporan.pdf', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status
                ]) }}"
                target="_blank"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold inline-block"
            >
                Cetak PDF
            </a>

            {{-- CETAK EXCEL --}}
            <a
                href="{{ route('petugas.laporan.excel', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status
                ]) }}"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold inline-block"
            >
                Cetak Excel
            </a>

        </form>

    </div>


    {{-- DATA LAPORAN --}}
    <div class="p-5">

        <div class="print-title hidden">

            <h1 class="text-xl font-bold text-center">
                LAPORAN TRANSAKSI PEMINJAMAN ALAT
            </h1>

            <p class="text-center text-sm">
                Sistem Peminjaman Alat Laboratorium
            </p>

            <br>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm border-collapse">

                <thead>

                    <tr class="bg-gray-50">

                        <th class="border px-3 py-2 text-left">
                            No
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Peminjam
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Tanggal Pinjam
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Rencana Kembali
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Alat
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Status
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Tanggal Kembali
                        </th>

                        <th class="border px-3 py-2 text-left">
                            Denda
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($peminjamans as $index => $peminjaman)

                        <tr>

                            <td class="border px-3 py-2">
                                {{ $index + 1 }}
                            </td>


                            <td class="border px-3 py-2">
                                {{ $peminjaman->user->name ?? '-' }}
                            </td>


                            <td class="border px-3 py-2">
                                {{ $peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}
                            </td>


                            <td class="border px-3 py-2">
                                {{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') ?? '-' }}
                            </td>


                            <td class="border px-3 py-2">

                                @forelse($peminjaman->detailPinjam as $detail)

                                    <div>
                                        {{ $detail->alat->nama_alat ?? '-' }}
                                        × {{ $detail->jumlah }}
                                    </div>

                                @empty

                                    -

                                @endforelse

                            </td>


                            <td class="border px-3 py-2">

                                {{ ucfirst($peminjaman->status) }}

                            </td>


                            <td class="border px-3 py-2">

                                {{ $peminjaman->pengembalian?->tgl_kembali?->format('d-m-Y') ?? '-' }}

                            </td>


                            <td class="border px-3 py-2">

                                Rp
                                {{ number_format(
                                    $peminjaman->pengembalian->denda ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="border px-3 py-8 text-center text-gray-500"
                            >

                                Tidak ada data laporan.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- PRINT CSS --}}
<style>

@media print {

    body {
        background: white !important;
    }

    aside,
    header,
    form,
    .shadow-sm {
        box-shadow: none !important;
    }

    aside,
    header {
        display: none !important;
    }

    main {
        padding: 0 !important;
    }

    .print-title {
        display: block !important;
    }

    table {
        width: 100% !important;
        font-size: 10px !important;
    }

    th,
    td {
        border: 1px solid #000 !important;
    }

}

</style>

@endsection