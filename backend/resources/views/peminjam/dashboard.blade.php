@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <a href="{{ route('peminjam.riwayat') }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500 hover:shadow-md data-aos="zoom-in" data-aos-delay="0">
        <div class="text-xs text-gray-500">Total Pinjam</div>
        <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
        <div class="text-xs text-blue-600">Lihat riwayat &rarr;</div>
    </a>
    <a href="{{ route('peminjam.riwayat', ['status'=>'diajukan']) }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500 hover:shadow-md" data-aos="zoom-in" data-aos-delay="50">
        <div class="text-xs text-gray-500">Menunggu</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['diajukan'] }}</div>
        <div class="text-xs text-yellow-600">Menunggu ACC</div>
    </a>
    <a href="{{ route('peminjam.riwayat', ['status'=>'dipinjam']) }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-600 hover:shadow-md" data-aos="zoom-in" data-aos-delay="100">
        <div class="text-xs text-gray-500">Dipinjam</div>
        <div class="text-2xl font-bold text-blue-600">{{ $stats['dipinjam'] }}</div>
        <div class="text-xs text-blue-600">Sedang dipinjam</div>
    </a>
    <a href="{{ route('peminjam.riwayat', ['status'=>'telat']) }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500 hover:shadow-md" data-aos="zoom-in" data-aos-delay="150">
        <div class="text-xs text-gray-500">Telat</div>
        <div class="text-2xl font-bold text-red-600">{{ $stats['telat'] }}</div>
        <div class="text-xs text-red-600">Segera kembalikan</div>
    </a>
    <a href="{{ route('peminjam.riwayat', ['status'=>'dikembalikan']) }}" class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-emerald-500 hover:shadow-md" data-aos="zoom-in" data-aos-delay="200">
        <div class="text-xs text-gray-500">Selesai</div>
        <div class="text-2xl font-bold text-emerald-600">{{ $stats['dikembalikan'] }}</div>
        <div class="text-xs text-emerald-600">Dikembalikan</div>
    </a>
</div>

@if($stats['telat'] > 0)
<div class="bg-red-50 border border-red-300 rounded-xl p-4 mb-4 text-sm text-red-800" data-aos="fade-up">
    <b>Telat {{ $stats['telat'] }} pengajuan!</b> Estimasi denda berjalan Rp{{ number_format($totalDenda,0,',','.') }} (Rp{{ number_format($dendaPerHari,0,',','.') }}/hari). Segera kembalikan alatnya.
</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800">Pengajuan Aktif</h3>
        <a href="{{ route('peminjam.katalog') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Pinjam Alat</a>
    </div>
    <div class="space-y-3">
        @forelse($aktif as $p)
            <div class="border rounded-xl p-4 flex flex-col md:flex-row md:items-center gap-3 hover:bg-gray-50" data-aos="fade-up">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($p->status === 'diajukan')
                            <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">Diajukan</span>
                        @elseif($p->status === 'dipinjam')
                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">Dipinjam</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-bold">Telat</span>
                        @endif
                        <span class="font-semibold text-gray-900 text-sm">{{ $p->nama_alat_list }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Rencana kembali: {{ $p->tgl_kembali_plan ? $p->tgl_kembali_plan->format('d-m-Y') : '-' }}
                        @if($p->status !== 'diajukan' && !is_null($p->sisa_hari))
                            @if($p->sisa_hari < 0)
                                <span class="text-red-600 font-bold">&bull; Telat {{ $p->lewat_hari }} hari (est. denda Rp{{ number_format($p->estimasi_denda,0,',','.') }})</span>
                            @elseif($p->sisa_hari <= 3)
                                <span class="text-yellow-600 font-bold">&bull; Tenggat {{ $p->sisa_hari }} hari lagi</span>
                            @else
                                &bull; Sisa {{ $p->sisa_hari }} hari
                            @endif
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('peminjam.riwayat') }}" class="text-xs bg-blue-100 hover:bg-blue-200 px-3 py-1.5 rounded-lg">Detail</a>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <div class="text-4xl mb-2">📋</div>
                <div class="text-gray-500 text-sm">Tidak ada pengajuan aktif.</div>
                <a href="{{ route('peminjam.katalog') }}" class="text-xs text-blue-600">Ajukan di katalog &rarr;</a>
            </div>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <div class="space-y-2">
        @forelse($notifs as $n)
            <a href="{{ $n->link ?? route('peminjam.riwayat') }}" class="block border rounded-xl px-4 py-3 hover:bg-gray-50">
                <div class="text-sm font-semibold text-gray-800">{{ $n->judul }}</div>
                <div class="text-xs text-gray-500">{{ $n->pesan }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $n->created_at ? $n->created_at->format('d-m-Y H:i') : '' }}</div>
            </a>
        @empty
            <div class="text-xs text-gray-400">Belum ada notifikasi.</div>
        @endforelse
    </div>
</div>
@endsection
