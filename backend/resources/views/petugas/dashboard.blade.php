@extends('layouts.app')
@section('title', 'Dashboard Petugas')
@section('header-title', 'Ringkasan Kerja Petugas')
@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center"><div class="text-xs text-yellow-700">Menunggu Verifikasi</div><div class="text-3xl font-extrabold text-yellow-700">{{ $stats['menunggu'] }}</div></div>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center"><div class="text-xs text-blue-700">Sedang Dipinjam</div><div class="text-3xl font-extrabold text-blue-700">{{ $stats['dipinjam'] }}</div></div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center"><div class="text-xs text-red-700">Telat</div><div class="text-3xl font-extrabold text-red-700">{{ $stats['telat'] }}</div></div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center"><div class="text-xs text-emerald-700">Kembali Hari Ini</div><div class="text-3xl font-extrabold text-emerald-700">{{ $stats['kembaliHariIni'] }}</div></div>
</div>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-bold text-gray-800 mb-2">Antrean:</h3>
        <ul class="text-sm text-gray-700 space-y-2">
            @forelse($antrean as $a)
            <li class="flex justify-between items-center border-b pb-2"><span>{{ $a->user->name ?? '-' }} &mdash; {{ $a->detailPinjam->first()->alat->nama_alat ?? '-' }}</span>@if($a->status == 'telat')<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-800">telat {{ $a->hari_telat }} hari</span>@elseif($a->status == 'dipinjam')<span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-800">sisa {{ $a->sisa_hari }} hari</span>@else<span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">diajukan</span>@endif</li>
            @empty
            <li class="text-gray-500">Tidak ada antrean.</li>
            @endforelse
        </ul>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-bold text-gray-800 mb-2">Shortcut</h3>
        <div class="flex flex-col gap-2">
            <a href="{{ route('petugas.peminjaman.index') }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg text-center">Kelola Peminjaman</a>
            <a href="{{ route('petugas.pengembalian.index') }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg text-center">Proses Pengembalian</a>
            <a href="{{ route('petugas.laporan.index') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg text-center">Laporan</a>
        </div>
    </div>
</div>
@endsection
