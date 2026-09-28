<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Models\Notifikasi;
use Illuminate\Console\Command;

class AutoCancelReqKembali extends Command
{
    protected $signature = 'peminjaman:auto-cancel-req-kembali';
    protected $description = 'Batalkan req_kembali yang tidak diproses > 24 jam, kembalikan ke dipinjam';

    public function handle(): int
    {
        $timeoutHours = 24;
        $cutoff = now()->subHours($timeoutHours);

        $reqs = Peminjaman::where('status', 'req_kembali')
            ->where('updated_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($reqs as $pinjam) {
            $pinjam->update(['status' => 'dipinjam']);
            $count++;

            Notifikasi::create([
                'user_id' => $pinjam->user_id,
                'judul' => 'Request Pengembalian Dibatalkan Otomatis',
                'pesan' => "Request pengembalian #{$pinjam->id} dibatalkan karena tidak diproses petugas dalam {$timeoutHours} jam. Status kembali ke 'Dipinjam'.",
                'link' => route('peminjam.riwayat'),
            ]);

            $stafIds = \App\Models\User::whereIn('role', ['admin', 'petugas'])->pluck('id');
            foreach ($stafIds as $sid) {
                Notifikasi::create([
                    'user_id' => $sid,
                    'judul' => 'Req Kembali Auto-Cancel #' . $pinjam->id,
                    'pesan' => "Request pengembalian #{$pinjam->id} dibatalkan otomatis (timeout {$timeoutHours} jam).",
                    'link' => route('petugas.pengembalian.index'),
                ]);
            }
        }

        $this->info("Auto-cancel req_kembali: {$count} peminjaman.");
        return self::SUCCESS;
    }
}