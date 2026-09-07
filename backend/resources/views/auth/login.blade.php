<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
        .fade-up { animation: fadeUp .5s ease both; }
        .fade-up-1 { animation-delay: .05s; }
        .fade-up-2 { animation-delay: .15s; }
        .fade-up-3 { animation-delay: .25s; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-slate-200 via-blue-100 to-indigo-200 relative overflow-hidden">
<div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-300/40 rounded-full blur-3xl pointer-events-none"></div>
<div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-300/40 rounded-full blur-3xl pointer-events-none"></div>

<div class="relative flex w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
    <div class="hidden md:flex w-1/2 bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-white p-10 flex-col justify-between">
        <div class="fade-up">
            <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center text-2xl mb-4">📦</div>
            <h1 class="text-3xl font-extrabold leading-tight">Sistem<br>Peminjaman Alat</h1>
            <p class="text-blue-200 text-sm mt-2">Peminjaman Alat — cepat, tercatat, terpantau.</p>
        </div>
        <ul class="space-y-3 text-sm fade-up fade-up-2">
            <li class="flex gap-2 items-center"><span class="w-6 h-6 bg-white/15 rounded-lg flex items-center justify-center">🛠</span> Admin: kelola alat, user &amp; stok</li>
            <li class="flex gap-2 items-center"><span class="w-6 h-6 bg-white/15 rounded-lg flex items-center justify-center">✅</span> Petugas: setujui &amp; catat kembali</li>
            <li class="flex gap-2 items-center"><span class="w-6 h-6 bg-white/15 rounded-lg flex items-center justify-center">🎒</span> Peminjam: katalog &amp; riwayat</li>
        </ul>
    </div>
    <div class="w-full md:w-1/2 p-10" x-data="{ lihat: false }">
        <h2 class="text-2xl font-extrabold text-gray-800 fade-up">Selamat datang 👋</h2>
        <p class="text-sm text-gray-500 mb-6 fade-up fade-up-1">Masuk untuk mengelola peminjaman.</p>
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm fade-up">⚠️ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm fade-up">
                <ul class="list-disc pl-5 mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('login') }}" method="POST" class="space-y-4 fade-up fade-up-2">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400">✉️</span>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@gmail.com"
                        class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400">🔒</span>
                    <input :type="lihat ? 'text' : 'password'" name="password" required placeholder="••••••••"
                        class="w-full pl-10 pr-14 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <button type="button" @click="lihat = !lihat" x-text="lihat ? 'Sembunyi' : 'Lihat'"
                        class="absolute right-3 top-2.5 text-xs text-blue-600 font-semibold"></button>
                </div>
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-2.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg hover:-translate-y-0.5 transition">Masuk →</button>
        </form>
    </div>
</div>
</body>
</html>
