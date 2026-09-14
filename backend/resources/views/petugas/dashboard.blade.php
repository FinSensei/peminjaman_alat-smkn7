@extends('layouts.app')
@section('title', 'Dashboard Petugas')
@section('header-title', 'Ringkasan Kerja Petugas')
@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center gap-3 shadow-sm card-lift"><span class="text-3xl">⏳</span><div><div class="text-2xl font-extrabold text-yellow-700">{{ $stats['menunggu'] }}</div><div class="text-xs text-yellow-700">Menunggu Verifikasi</div></div></div>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-3 shadow-sm card-lift"><span class="text-3xl">📦</span><div><div class="text-2xl font-extrabold text-blue-700">{{ $stats['dipinjam'] }}</div><div class="text-xs text-blue-700">Sedang Dipinjam</div></div></div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 shadow-sm card-lift"><span class="text-3xl">⛔</span><div><div class="text-2xl font-extrabold text-red-700">{{ $stats['telat'] }}</div><div class="text-xs text-red-700">Telat</div></div></div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 shadow-sm card-lift"><span class="text-3xl">✅</span><div><div class="text-2xl font-extrabold text-emerald-700">{{ $stats['kembaliHariIni'] }}</div><div class="text-xs text-emerald-700">Kembali Hari Ini</div></div></div>
</div>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-bold text-gray-800 mb-3">Antrean: Diajukan, Tenggat dan Telat</h3>
        <div class="space-y-2">
            @forelse($antrean as $a)
            <div class="border-l-4 {{ $a->status == 'telat' ? 'border-red-500 bg-red-50/50' : ($a->status == 'dipinjam' ? 'border-orange-400 bg-orange-50/50' : 'border-yellow-400 bg-yellow-50/50') }} rounded-lg p-3 flex justify-between items-center shadow-sm">
                <div class="min-w-0"><div class="text-sm font-semibold text-gray-800 truncate">{{ $a->user->name ?? '-' }}</div><div class="text-xs text-gray-500 truncate">{{ $a->detailPinjam->first()->alat->nama_alat ?? '-' }}</div></div>
                @if($a->status == 'telat')<span class="shrink-0 ml-2 text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-semibold">telat {{ $a->hari_telat }} hari</span>@elseif($a->status == 'dipinjam')<span class="shrink-0 ml-2 text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 font-semibold">sisa {{ $a->sisa_hari }} hari</span>@else<span class="shrink-0 ml-2 text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 font-semibold">diajukan</span>@endif
            </div>
            @empty
            <div class="text-sm text-gray-500 text-center py-4">Tidak ada antrean.</div>
            @endforelse
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-bold text-gray-800 mb-3">Jalan Pintas</h3>
        <div class="flex flex-col gap-2">
            <a href="{{ route('petugas.peminjaman.index') }}" class="px-4 py-2.5 bg-gray-800 text-white text-sm font-semibold rounded-lg text-center shadow-sm">📋 Kelola Peminjaman</a>
            <a href="{{ route('petugas.pengembalian.index') }}" class="px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg text-center shadow-sm">📦 Proses Pengembalian</a>
            <a href="{{ route('petugas.laporan.index') }}" class="px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg text-center shadow-sm">🖨 Laporan</a>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl border border-gray-200 mt-4 overflow-hidden">
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <h3 class="font-bold text-gray-800">Pengembalian Terbaru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                    <th class="py-3 px-4 border-b text-left">Peminjam</th>
                    <th class="py-3 px-4 border-b text-left">Alat</th>
                    <th class="py-3 px-4 border-b text-left">Tgl Kembali</th>
                    <th class="py-3 px-4 border-b text-left">Kondisi</th>
                    <th class="py-3 px-4 border-b text-left">Denda</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($riwayatBaru as $r)
                <tr class="hover:bg-gray-50">
                <td class='py-3 px-4 border-b font-medium'>{{ $r->peminjaman->user->name ?? '-' }}</td>
                <td class='py-3 px-4 border-b'>{{ $r->peminjaman->detailPinjam->first()->alat->nama_alat ?? '-' }}</td>
                <td class='py-3 px-4 border-b'>{{ $r->tgl_kembali?->format('d-m-Y') ?? '-' }}</td>
                <td class='py-3 px-4 border-b'>{{ $r->kondisi_kembali }}</td>
                <td class='py-3 px-4 border-b font-semibold {{ $r->denda > 0 ? 'text-red-600' : 'text-emerald-600' }}'>Rp{{ number_format($r->denda, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Belum ada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection