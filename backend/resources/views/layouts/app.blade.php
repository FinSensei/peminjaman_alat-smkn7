<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>
        <script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
.card-lift { transition: transform .25s ease, box-shadow .25s ease; }
.card-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -8px rgba(0,0,0,.25); }
</style>
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="bg-gradient-to-b from-gray-900 to-gray-800 text-white flex flex-col hidden md:flex w-64 flex-shrink-0">
        @if(auth()->user()->role === 'admin')
        <div class="p-5 border-b border-gray-400 flex items-center gap-3">
            <img src="{{ asset('admin.png') }}" alt="Logo Admin" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 bg-white/10">
            <div>
                <div class="text-sm font-extrabold tracking-wider">PANEL ADMIN</div>
                <div class="text-[11px] text-gray-400">Peminjaman Alat</div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <p class="px-4 pt-1 text-[11px] font-bold uppercase tracking-wider text-gray-500">Master Data</p>
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">🏠</span>Dashboard</a>
            <a href="{{ route('admin.alat.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.alat*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">📦</span>Kelola Alat</a>
            <a href="{{ route('admin.user.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.user*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">👥</span>Kelola User</a>
            <a href="{{ route('admin.kategori.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.kategori*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">📁</span>Kelola Kategori</a>
            <p class="px-4 pt-2 text-[11px] font-bold uppercase tracking-wider text-gray-500">Transaksi</p>
            <a href="{{ route('admin.peminjaman.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.peminjaman*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">🔄</span>Kelola Peminjaman</a>
            <a href="{{ route('admin.pengembalian.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.pengembalian*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">↩️</span>Kelola Pengembalian</a>
        </nav>
        <div class="p-4 border-t border-gray-400 flex items-center gap-3">
            @if(auth()->user()->foto_profile)
                <img src="{{ asset('storage/'.auth()->user()->foto_profile) }}" alt="Foto" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white flex-shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div class="min-w-0">
                <div class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-600/30 text-blue-200 font-bold">ADMIN</span>
            </div>
        </div>
        @endif

        <!-- MENU KHUSUS PETUGAS -->
        @if(auth()->user()->role === 'petugas')
        <div class="p-5 border-b border-gray-400 flex items-center gap-3">
            <img src="{{ asset('petugas.png') }}" alt="Logo Petugas" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 bg-white/10">
            <div>
                <div class="text-sm font-extrabold tracking-wider">PANEL PETUGAS</div>
                <div class="text-[11px] text-gray-400">Peminjaman Alat</div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('petugas.dashboard') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.dashboard') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">🏠</span>Dashboard</a>
        <a href="{{ route('petugas.peminjaman.index') }}"
        class="block px-4 py-2 rounded-lg transition {{
        request()->routeIs('petugas.peminjaman*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">✅</span>Persetujuan Peminjaman</a>

        <a href="{{ route('petugas.pengembalian.index') }}"
        class="block px-4 py-2 rounded-lg transition {{
        request()->routeIs('petugas.pengembalian*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">📋</span>Pemantauan Pengembalian</a>

        <a href="{{ route('petugas.laporan.index') }}"
        class="block px-4 py-2 rounded-lg transition {{
        request()->routeIs('petugas.laporan*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">🖨️</span>Cetak Laporan</a>
        </nav>
        <div class="p-4 border-t border-gray-400 flex items-center gap-3">
            @if(auth()->user()->foto_profile)
                <img src="{{ asset('storage/'.auth()->user()->foto_profile) }}" alt="Foto" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white flex-shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div class="min-w-0">
                <div class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-600/30 text-blue-200 font-bold">PETUGAS</span>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'peminjam')
        <div class="p-5 border-b border-gray-400 flex items-center gap-3">
            <img src="{{ asset('peminjam.png') }}" alt="Logo Peminjam" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 bg-white/10">
            <div>
                <div class="text-sm font-extrabold tracking-wider">PANEL PEMINJAM</div>
                <div class="text-[11px] text-gray-400">Peminjaman Alat</div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-2">
        <a href="{{ route('peminjam.katalog') }}"
        class="block px-4 py-2 rounded-lg transition {{
        request()->routeIs('peminjam.katalog*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">📦</span>Katalog Alat</a>

        <a href="{{ route('peminjam.riwayat') }}"
        class="block px-4 py-2 rounded-lg transition {{
        request()->routeIs('peminjam.riwayat*') ? 'bg-gray-900 text-white font-medium shadow border-l-4 border-blue-500' : 'text-gray-400 hover:bg-gray-700 hover:text-white border-l-4 border-transparent' }}"><span class="mr-2">🕘</span>Riwayat Saya</a>
        </nav>
        <div class="p-4 border-t border-gray-400 flex items-center gap-3">
            @if(auth()->user()->foto_profile)
                <img src="{{ asset('storage/'.auth()->user()->foto_profile) }}" alt="Foto" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white flex-shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div class="min-w-0">
                <div class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-600/30 text-blue-200 font-bold">PEMINJAM</span>
            </div>
        </div>
        @endif
        <div class="p-4 pt-0">
            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Anda yakin ingin logout?')">
                @csrf
                <button type="submit" class="w-full bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                    <span>🚪</span> Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT CONTAINER -->
    <div id="konten-scroll" class="flex-1 flex flex-col overflow-y-auto">

        <!-- NAVBAR ATAS -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 pt-2 z-10">
            <div class="text-lg font-semibold text-gray-800">
                @yield('header-title', 'Dashboard')
            </div>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
                @php($notifBelum = \App\Models\Notifikasi::where('user_id', auth()->id())->where('dibaca', false)->count())
                @php($notifList = \App\Models\Notifikasi::where('user_id', auth()->id())->latest()->take(5)->get())
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="relative text-xl leading-none" title="Notifikasi">&#128276;
                        @if($notifBelum)
                        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5">{{ $notifBelum }}</span>
                        @endif
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden z-50">
                        <div class="px-4 py-2 border-b font-bold text-sm text-gray-800 flex justify-between items-center">
                            <span>Notifikasi</span>
                            <form action="{{ route('notifikasi.bacaSemua') }}" method="POST">@csrf<button class="text-xs text-blue-600 font-semibold">Tandai dibaca</button></form>
                        </div>
                        @forelse($notifList as $notif)
                        <a href="{{ route('notifikasi.buka', $notif->id) }}" class="block px-4 py-2 border-b hover:bg-gray-50 {{ $notif->dibaca ? '' : 'bg-blue-50' }}">
                            <div class="text-sm font-semibold text-gray-800">{{ $notif->judul }}</div>
                            <div class="text-xs text-gray-600">{{ $notif->pesan }}</div>
                            <div class="text-[10px] text-gray-400">{{ $notif->created_at->diffForHumans() }}</div>
                        </a>
                        @empty
                        <div class="px-4 py-4 text-sm text-gray-500 text-center">Belum ada notifikasi.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </header>

        <!-- KONTEN UTAMA HALAMAN -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 500, once: true });</script>
<script>let aosT;document.getElementById("konten-scroll").addEventListener("scroll",()=>{clearTimeout(aosT);aosT=setTimeout(()=>window.dispatchEvent(new Event("scroll")),80);},{passive:true});</script>
<script>
function formKembali(plan, tarif) {
    return {
        buka: false, status: 'dikembalikan', denda: 0, plan: plan, tarif: tarif,
        pilih(s) {
            this.status = s; this.buka = true;
            let telat = 0;
            if (this.plan) {
                const p = new Date(this.plan); p.setHours(0,0,0,0);
                const t = new Date(); t.setHours(0,0,0,0);
                telat = Math.max(0, Math.round((t - p) / 86400000));
            }
            this.denda = (s === 'telat') ? Math.max(1, telat) * this.tarif : 0;
        }
    };
}
</script>

</body>
</html>
