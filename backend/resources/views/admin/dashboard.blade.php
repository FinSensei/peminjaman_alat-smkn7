@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    <!-- Alert Selamat Datang -->
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
        Selamat datang, <strong class="font-semibold">{{ auth()->user()->name }}</strong>! Anda login sebagai hak akses
        <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6 justify-center" data-aos="fade-down">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center card-lift"><div class="text-xs text-gray-500">Total Alat</div><div class="text-2xl font-extrabold">{{ $stats['alat'] }}</div></div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center card-lift"><div class="text-xs text-blue-700">User</div><div class="text-2xl font-extrabold text-blue-700">{{ $stats['user'] }}</div></div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center card-lift"><div class="text-xs text-yellow-700">Aktif</div><div class="text-2xl font-extrabold text-yellow-700">{{ $stats['aktif'] }}</div></div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center card-lift"><div class="text-xs text-red-700">Telat</div><div class="text-2xl font-extrabold text-red-700">{{ $stats['telat'] }}</div></div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center card-lift"><div class="text-xs text-emerald-700">Kembali Hari Ini</div><div class="text-2xl font-extrabold text-emerald-700">{{ $stats['kembaliHariIni'] }}</div></div>
    </div>

    <!-- Grafik Statistik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <div class="grid grid-cols-1 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 max-w-4xl mx-auto w-full">
            <h3 class="font-bold text-gray-800 mb-2">Peminjaman 6 Bulan Terakhir</h3>
            <canvas id="chBulan" height="100"
                data-labels='@json($grafikBulan->pluck('bl'))'
                data-values='@json($grafikBulan->pluck('jml'))'></canvas>
        </div>
    </div>
    <script>
        const chartElement = document.getElementById('chBulan');
        const chartLabels = JSON.parse(chartElement.dataset.labels);
        const chartData = JSON.parse(chartElement.dataset.values);

        new Chart(chartElement, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Peminjaman',
                    data: chartData,
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>

    {{-- TAMBAHAN: ReqEdit menunggu (sisip di atas Log, tidak ubah posisi log lama) --}}
    @if(isset($reqEditCount) && $reqEditCount > 0)
        <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <span class="font-bold">{{ $reqEditCount }} permintaan perbaikan menunggu persetujuan</span>
                <span class="text-sm"> — ada permintaan perbaikan dari Petugas</span>
            </div>
            <a href="{{ route('admin.pengembalian.index', ['filter'=>'perbaikan']) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg">Lihat</a>
        </div>
    @endif
    @if(isset($reqEdits) && $reqEdits->count() > 0)
        <div class="mb-6 bg-white rounded-lg shadow-sm border border-amber-200 overflow-hidden">
            <div class="p-4 border-b bg-amber-50 flex items-center justify-between">
                <h3 class="font-bold text-amber-800">Permintaan Perbaikan Terbaru</h3>
                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">{{ $reqEdits->count() }} pending</span>
            </div>
            <div class="divide-y">
                @foreach($reqEdits as $r)
                    <div class="p-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-800">#{{ $r->id }} — {{ $r->peminjaman->user->name ?? '-' }} ({{ $r->kondisi_kembali }})</div>
                            <div class="text-xs text-gray-600 mt-1">Petugas: {{ $r->petugas->name ?? '-' }} — "{{ \Illuminate\Support\Str::limit($r->catatan_perbaikan, 80) }}"</div>
                            <div class="text-[11px] text-gray-400">{{ $r->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <form action="{{ route('admin.pengembalian.approve', $r->id) }}" method="POST">@csrf<button class="px-3 py-1 bg-emerald-600 text-white text-xs rounded">Setujui</button></form>
                            <form action="{{ route('admin.pengembalian.reject', $r->id) }}" method="POST">@csrf<button class="px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded">Tolak</button></form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tabel Log Aktivitas -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Log Aktivitas Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Waktu</th>
                        <th class="py-3 px-4 border-b">User</th>
                        <th class="py-3 px-4 border-b">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b">{{ $log->created_at }}</td>
                            <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-4 border-b">{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">Belum ada log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
