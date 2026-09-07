<?php

namespace App\Observers;

use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    private function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
    }

    public function created(User $user): void
    {
        $this->catatLog(
            "Menambahkan user baru: {$user->name} (ID: #{$user->id})"
        );
    }

    public function updated(User $user): void
    {
        $perubahan = array_diff(
            array_keys($user->getChanges()),
            ['updated_at']
        );

        if (!empty($perubahan)) {

            $kolom = implode(', ', $perubahan);

            $this->catatLog(
                "Memperbarui data user '{$user->name}' " .
                "(ID: #{$user->id}, Kolom yang diubah: {$kolom})"
            );
        }
    }

    public function deleted(User $user): void
    {
        $this->catatLog(
            "Menghapus user: {$user->name} (ID: #{$user->id})"
        );
    }
}