<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Models\Notifikasi;
use Illuminate\Console\Command;

class TandaiTelat extends Command
{
    protected $signature = 'peminjaman:tandai-telat';
    protected $description = 'Ubah status dipinjam menjadi telat bila lewat tgl_kembali_plan';

    public function handle(): int
    {
        $n = Peminjaman::where('status', 'dipinjam')
            ->whereDate('tgl_kembali_plan', '<', now()->toDateString())
            ->update(['status' => 'telat']);
        $this->info("Ditandai telat: {$n} peminjaman.");

        // NOTIF H-3: dipinjam & tenggat tepat 3 hari lagi (1x per hari)
        $h3 = Peminjaman::where('status', 'dipinjam')
            ->whereDate('tgl_kembali_plan', now()->addDays(3)->toDateString())->get();
        foreach ($h3 as $pm) {
            $ada = Notifikasi::where('user_id', $pm->user_id)
                ->where('judul', 'Jatuh tempo H-3')
                ->whereDate('created_at', now()->toDateString())->exists();
            if (!$ada) {
                Notifikasi::create([
                    'user_id' => $pm->user_id,
                    'judul' => 'Jatuh tempo H-3',
                    'pesan' => "Peminjaman #{$pm->id} jatuh tempo 3 hari lagi (" . now()->addDays(3)->translatedFormat('d F Y') . ").",
                    'link' => '/peminjam/riwayat',
                ]);
            }
        }

        // NOTIF LEWAT TENGGAT: status telat (1x per hari)
        $telat = Peminjaman::where('status', 'telat')->get();
        foreach ($telat as $pm) {
            $ada = Notifikasi::where('user_id', $pm->user_id)
                ->where('judul', 'Melewati jatuh tempo')
                ->whereDate('created_at', now()->toDateString())->exists();
            if (!$ada) {
                Notifikasi::create([
                    'user_id' => $pm->user_id,
                    'judul' => 'Melewati jatuh tempo',
                    'pesan' => "Peminjaman #{$pm->id} melewati tenggat, denda Rp5.000/hari berjalan.",
                    'link' => '/peminjam/riwayat',
                ]);
            }
        }
        $this->info("Notif dicek: H-3={$h3->count()}, telat={$telat->count()}.");
        return self::SUCCESS;
    }
}
