@extends('layouts.app')

@section('title', 'Proses Pengembalian - Panel Admin')

@section('header-title', 'Proses Pengembalian')

@section('content')

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('error') }}
    </div>
@endif


<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        {{-- Header --}}
        <div class="p-5 border-b border-gray-200">

            <h2 class="text-lg font-bold text-gray-800">
                Form Pengembalian Alat
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Silakan periksa kondisi alat sebelum memproses pengembalian.
            </p>

        </div>


        <div class="p-5">

            {{-- Informasi Peminjam --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">

                <h3 class="font-semibold text-gray-800 mb-3">
                    Informasi Peminjaman
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    <div>

                        <span class="text-gray-500">
                            Peminjam
                        </span>

                        <p class="font-semibold text-gray-800">
                            {{ $peminjaman->user->name ?? '-' }}
                        </p>

                    </div>


                    <div>

                        <span class="text-gray-500">
                            Email
                        </span>

                        <p class="font-semibold text-gray-800">
                            {{ $peminjaman->user->email ?? '-' }}
                        </p>

                    </div>


                    <div>

                        <span class="text-gray-500">
                            Tanggal Pinjam
                        </span>

                        <p class="font-semibold text-gray-800">
                            {{ $peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}
                        </p>

                    </div>


                    <div>

                        <span class="text-gray-500">
                            Rencana Kembali
                        </span>

                        <p class="font-semibold text-gray-800">
                            {{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') ?? '-' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Daftar alat --}}
            <div class="mb-6">

                <h3 class="font-semibold text-gray-800 mb-3">
                    Alat yang Dipinjam
                </h3>


                <div class="border border-gray-200 rounded-lg overflow-hidden">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="py-3 px-4 text-left border-b">
                                    Nama Alat
                                </th>

                                <th class="py-3 px-4 text-center border-b">
                                    Jumlah
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($peminjaman->detailPinjam as $detail)

                                <tr>

                                    <td class="py-3 px-4 border-b">
                                        {{ $detail->alat->nama_alat ?? '-' }}
                                    </td>

                                    <td class="py-3 px-4 border-b text-center">
                                        {{ $detail->jumlah }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- Form --}}
            <form
                action="{{ route('admin.pengembalian.store', $peminjaman->id) }}"
                method="POST"
            >

                @csrf


                {{-- Tanggal kembali --}}
                <div class="mb-5">

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Kembali
                    </label>

                    <input
                        type="date"
                        name="tgl_kembali"
                        value="{{ old('tgl_kembali', date('Y-m-d')) }}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >

                    @error('tgl_kembali')
                        <p class="text-red-500 text-xs mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Kondisi --}}
                <div class="mb-5">

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kondisi Alat Saat Kembali
                    </label>

                    <select
                        name="kondisi_kembali"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >

                        <option value="">
                            -- Pilih Kondisi --
                        </option>

                        <option
                            value="Baik"
                            {{ old('kondisi_kembali') == 'Baik' ? 'selected' : '' }}
                        >
                            Baik
                        </option>

                        <option
                            value="Rusak Ringan"
                            {{ old('kondisi_kembali') == 'Rusak Ringan' ? 'selected' : '' }}
                        >
                            Rusak Ringan
                        </option>

                        <option
                            value="Rusak Berat"
                            {{ old('kondisi_kembali') == 'Rusak Berat' ? 'selected' : '' }}
                        >
                            Rusak Berat
                        </option>

                        <option
                            value="Hilang"
                            {{ old('kondisi_kembali') == 'Hilang' ? 'selected' : '' }}
                        >
                            Hilang
                        </option>

                    </select>

                    @error('kondisi_kembali')
                        <p class="text-red-500 text-xs mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Denda --}}
                <div class="mb-6">

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Denda
                    </label>

                    <div class="relative">

                        <span class="absolute left-3 top-2 text-gray-500">
                            Rp
                        </span>

                        <input
                            type="number"
                            name="denda"
                            value="{{ old('denda', 0) }}"
                            min="0"
                            required
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >

                    </div>

                    @error('denda')
                        <p class="text-red-500 text-xs mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Tombol --}}
                <div class="flex justify-end gap-3">

                    <a
                        href="{{ route('admin.pengembalian.index') }}"
                        class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold"
                    >
                        Batal
                    </a>


                    <button
                        type="submit"
                        onclick="return confirm('Apakah alat sudah diperiksa dan ingin memproses pengembalian ini?')"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold"
                    >
                        Proses Pengembalian
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection