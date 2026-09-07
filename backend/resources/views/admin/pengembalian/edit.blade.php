@extends('layouts.app')

@section('title', 'Edit Pengembalian - Panel Admin')
@section('header-title', 'Edit Data Pengembalian')

@section('content')

<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">

    {{-- Judul --}}
    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-800">
            Edit Data Pengembalian
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Ubah kondisi alat dan denda pengembalian.
        </p>
    </div>


    {{-- Informasi Peminjaman --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">

        <h3 class="text-sm font-bold text-gray-700 mb-3">
            Informasi Peminjaman
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Peminjam --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    Peminjam
                </label>

                <div class="text-sm font-semibold text-gray-800">
                    {{ $pengembalian->peminjaman->user->name ?? '-' }}
                </div>

                <div class="text-xs text-gray-500">
                    {{ $pengembalian->peminjaman->user->email ?? '-' }}
                </div>
            </div>


            {{-- Petugas --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    Petugas
                </label>

                <div class="text-sm font-semibold text-gray-800">
                    {{ $pengembalian->petugas->name ?? '-' }}
                </div>
            </div>


            {{-- Tanggal Pinjam --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    Tanggal Pinjam
                </label>

                <div class="text-sm text-gray-800">
                    {{ $pengembalian->peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}
                </div>
            </div>


            {{-- Tanggal Kembali --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    Tanggal Kembali
                </label>

                <div class="text-sm text-gray-800">
                    {{ $pengembalian->tgl_kembali?->format('d-m-Y') ?? '-' }}
                </div>
            </div>

        </div>


        {{-- Alat --}}
        <div class="mt-4">

            <label class="block text-xs font-semibold text-gray-500 mb-2">
                Alat Dipinjam
            </label>

            @forelse($pengembalian->peminjaman->detailPinjam as $detail)

                <div class="text-sm text-gray-800 mb-1">

                    <span class="font-medium">
                        {{ $detail->alat->nama_alat ?? '-' }}
                    </span>

                    <span class="text-xs text-gray-500">
                        × {{ $detail->jumlah }}
                    </span>

                </div>

            @empty

                <span class="text-sm text-gray-400">
                    Tidak ada alat
                </span>

            @endforelse

        </div>

    </div>


    {{-- Form --}}
    <form
        action="{{ route('admin.pengembalian.update', $pengembalian->id) }}"
        method="POST"
    >

        @csrf
        @method('PUT')


        {{-- Kondisi Kembali --}}
        <div class="mb-5">

            <label
                for="kondisi_kembali"
                class="block text-gray-700 text-sm font-semibold mb-2"
            >
                Kondisi Kembali
            </label>

            @php
                $kondisiLama = old(
                    'kondisi_kembali',
                    $pengembalian->kondisi_kembali
                );

                $kondisiStandar = ['Baik', 'Lecet', 'Rusak'];

                $isKustom = !in_array($kondisiLama, $kondisiStandar);
            @endphp


            <select
                name="kondisi_kembali"
                id="kondisi_kembali"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                onchange="toggleKondisiCustom()"
            >

                <option value="Baik"
                    {{ $kondisiLama === 'Baik' ? 'selected' : '' }}>
                    Baik
                </option>

                <option value="Lecet"
                    {{ $kondisiLama === 'Lecet' ? 'selected' : '' }}>
                    Lecet
                </option>

                <option value="Rusak"
                    {{ $kondisiLama === 'Rusak' ? 'selected' : '' }}>
                    Rusak
                </option>

                <option value="Kustom"
                    {{ $isKustom ? 'selected' : '' }}>
                    Kustom
                </option>

            </select>

            @error('kondisi_kembali')
                <span class="text-red-500 text-xs mt-1 block">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- Kondisi Kustom --}}
        <div
            id="kondisi-custom-wrapper"
            class="{{ $isKustom ? '' : 'hidden' }} mb-5"
        >

            <label
                for="kondisi_custom"
                class="block text-gray-700 text-sm font-semibold mb-2"
            >
                Kondisi Kustom
            </label>

            <input
                type="text"
                name="kondisi_custom"
                id="kondisi_custom"
                value="{{ $isKustom ? old('kondisi_custom', $kondisiLama) : old('kondisi_custom') }}"
                placeholder="Contoh: Kabel putus, casing retak, tombol rusak..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >

            <p class="text-xs text-gray-500 mt-1">
                Tuliskan kondisi alat secara manual.
            </p>

            @error('kondisi_custom')
                <span class="text-red-500 text-xs mt-1 block">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- Denda --}}
        <div class="mb-6">

            <label
                for="denda"
                class="block text-gray-700 text-sm font-semibold mb-2"
            >
                Denda
            </label>

            <div class="relative">

                <span class="absolute left-3 top-2 text-gray-500 text-sm">
                    Rp
                </span>

                <input
                    type="number"
                    name="denda"
                    id="denda"
                    value="{{ old('denda', $pengembalian->denda) }}"
                    min="0"
                    required
                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

            </div>

            <p class="text-xs text-gray-500 mt-1">
                Masukkan 0 jika tidak ada denda.
            </p>

            @error('denda')
                <span class="text-red-500 text-xs mt-1 block">
                    {{ $message }}
                </span>
            @enderror

        </div>


        {{-- Tombol --}}
        <div class="flex justify-end space-x-2">

            <a
                href="{{ route('admin.pengembalian.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition"
            >
                Batal
            </a>

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
            >
                Perbarui
            </button>

        </div>

    </form>

</div>


{{-- Script Kustom --}}
<script>

function toggleKondisiCustom() {

    const select = document.getElementById('kondisi_kembali');
    const wrapper = document.getElementById('kondisi-custom-wrapper');
    const input = document.getElementById('kondisi_custom');

    if (select.value === 'Kustom') {

        wrapper.classList.remove('hidden');
        input.required = true;

    } else {

        wrapper.classList.add('hidden');
        input.required = false;
        input.value = '';

    }

}

document.addEventListener('DOMContentLoaded', function () {
    toggleKondisiCustom();
});

</script>

@endsection