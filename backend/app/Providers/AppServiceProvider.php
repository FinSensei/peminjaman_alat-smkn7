<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\Pengembalian;

use App\Observers\AlatObserver;
use App\Observers\KategoriObserver;
use App\Observers\UserObserver;
use App\Observers\PeminjamanObserver;
use App\Observers\PengembalianObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Alat::observe(AlatObserver::class);
        Kategori::observe(KategoriObserver::class);
        User::observe(UserObserver::class);
        Peminjaman::observe(PeminjamanObserver::class);
        Pengembalian::observe(PengembalianObserver::class);
    }
}