@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')
@section('header-title', 'Riwayat Peminjaman')

@section('content')
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="fixed top-20 right-6 z-50 bg-green-600 text-white pl-4 pr-3 py-3 rounded-xl shadow-lg flex gap-3 items-center max-w-sm">
    <span class="text-sm">{{ session('success') }}</span>
    <button @click="show = false" class="font-bold text-lg leading-none px-1">&times;</button>
</div>
@endif
@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition class="fixed top-20 right-6 z-50 bg-red-600 text-white pl-4 pr-3 py-3 rounded-xl shadow-lg flex gap-3 items-center max-w-sm">
    <span class="text-sm">{{ session('error') }}</span>
    <button @click="show = false" class="font-bold text-lg leading-none px-1">&times;</button>
</div>
@endif

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6" data-aos="fade-down">
    <div class="bg-white rounded-xl shadow-sm p-3 text-center">
        <div class="text-xs text-gray-500">Total</div>
        <div class="text-xl font-bold">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-center">
        <div class="text-xs text-yellow-700">Diajukan</div>
        <div class="text-xl font-bold text-yellow-700">{{ $stats['diajukan'] }}</div>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
        <div class="text-xs text-blue-700">Dipinjam</div>
        <div class="text-xl font-bold text-blue-700">{{ $stats['dipinjam'] }}</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
        <div class="text-xs text-red-700">Telat</div>
        <div class="text-xl font-bold text-red-700">{{ $stats['telat'] }}</div>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
        <div class="text-xs text-green-700">Dikembalikan</div>
        <div class="text-xl font-bold text-green-700">{{ $stats['dikembalikan'] }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Saya</h3>
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari alat..." class="border rounded-lg px-3 py-2 text-sm w-40">
                <select name="status" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <option value="diajukan" {{ ($status ?? '')=='diajukan'?'selected':'' }}>Diajukan</option>
                    <option value="dipinjam" {{ ($status ?? '')=='dipinjam'?'selected':'' }}>Dipinjam</option>
                    <option value="telat" {{ ($status ?? '')=='telat'?'selected':'' }}>Telat</option>
                    <option value="dikembalikan" {{ ($status ?? '')=='dikembalikan'?'selected':'' }}>Dikembalikan</option>
                </select>
                <button type="submit" class="bg-gray-800 text-white px-3 py-2 rounded-lg text-sm">Cari</button>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($peminjaman as $pinjam)
            <div class="border rounded-xl p-4 hover:shadow-md transition card-lift">
                <div class="flex flex-wrap justify-between gap-2 mb-3">
                    <div>
                        <span class="text-sm font-semibold text-gray-800">Peminjaman #{{ $semuaId->count() - $semuaId->search($pinjam->id) }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $pinjam->tgl_pinjam }} &rarr; {{ $pinjam->tgl_kembali_plan }}</span>
                    </div>
                    @php
                        $badge = ['diajukan'=>'bg-yellow-100 text-yellow-800','dipinjam'=>'bg-blue-100 text-blue-800','telat'=>'bg-red-100 text-red-800','dikembalikan'=>'bg-emerald-100 text-emerald-800'][$pinjam->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ ucfirst($pinjam->status) }}</span>
                </div>
                @if($pinjam->status === 'dipinjam')
                    @php
                        $sisaHari = \Carbon\Carbon::parse(now()->toDateString())->diffInDays(\Carbon\Carbon::parse($pinjam->tgl_kembali_plan), false);
                    @endphp
                    @if($sisaHari > 0)
                    <div class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-1.5 mb-3">Jatuh tempo {{ $sisaHari }} hari lagi ({{ $pinjam->tgl_kembali_plan }})</div>
                    @else
                    <div class="text-xs text-orange-700 bg-orange-50 border border-orange-200 rounded-lg px-3 py-1.5 mb-3">Jatuh tempo hari ini, segera kembalikan!</div>
                    @endif
                @elseif($pinjam->status === 'telat')
                    @php
                        $hariTelat = \Carbon\Carbon::parse($pinjam->tgl_kembali_plan)->diffInDays(now()->toDateString());
                    @endphp
                    <div class="text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5 mb-3">Telat {{ $hariTelat }} hari &mdash; denda berjalan <b>Rp{{ number_format($hariTelat * $dendaPerHari, 0, ',', '.') }}</b></div>
                @endif

                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="text-left py-1">Alat</th>
                                <th class="text-center py-1 w-20">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pinjam->detailPinjam as $d)
                            <tr class="border-t">
                                <td class="py-2">{{ $d->alat->nama_alat ?? 'Alat dihapus' }}</td>
                                <td class="py-2 text-center font-bold">{{ $d->jumlah }} unit</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($pinjam->pengembalian)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm mb-3">
                        <div class="font-medium text-green-800">Sudah dikembalikan</div>
                        <div class="text-green-700 text-xs">Tgl: {{ $pinjam->pengembalian->tgl_kembali }} | Kondisi: {{ $pinjam->pengembalian->kondisi_kembali }} | Denda: Rp {{ number_format($pinjam->pengembalian->denda,0,',','.') }}</div>
                    </div>
                @endif

                @if($pinjam->status === 'diajukan')
                <div class="flex gap-2">
                        <form action="{{ route('peminjam.peminjaman.batal', $pinjam->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium">Batalkan</button>
                        </form>
                </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12">
                <div class="text-4xl mb-2">📋</div>
                <div class="text-gray-400 text-sm mb-2">Belum ada riwayat</div>
                <a href="{{ route('peminjam.katalog') }}" class="text-blue-600 text-sm font-medium hover:underline">Mulai pinjam sekarang &rarr;</a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $peminjaman->links() }}
    </div>
</div>
@endsection