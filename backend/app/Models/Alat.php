<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Alat extends Model
{
    protected $table = 'alat';

    protected $fillable = [
        'kategori_id', 'nama_alat', 'stok', 'stok_baik', 'stok_rusak', 'status_kondisi', 'deskripsi', 'gambar'
    ];

    protected function casts(): array {
        return [
            'stok' => 'integer',
            'stok_baik' => 'integer',
            'stok_rusak' => 'integer',
        ];
    }

    public function kategori(): BelongsTo {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPinjam(): HasMany {
        return $this->hasMany(DetailPinjam::class);
    }

    public function scopeTersedia($query)
    {
        return $query->where('stok_baik', '>', 0);
    }

    // Check if stok_baik + stok_rusak matches stok
    public function isStokConsistent(): bool
    {
        return $this->stok_baik + $this->stok_rusak === $this->stok;
    }

    // Jumlah unit yang sedang dipinjam (status: dipinjam, req_kembali, telat)
    public function jumlahDipinjamAktif(): int
    {
        if (! $this->id) {
            return 0;
        }

        return (int) DetailPinjam::where('alat_id', $this->id)
            ->whereHas('peminjaman', function ($q) {
                $q->whereIn('status', ['dipinjam', 'req_kembali', 'telat']);
            })
            ->sum('jumlah');
    }

    // Path relatif gambar di disk "public" (menormalkan awalan storage/)
    public function gambarRelativePath(): ?string
    {
        if (empty($this->gambar)) {
            return null;
        }

        $path = ltrim($this->gambar, '/');

        return Str::startsWith($path, 'storage/') ? Str::after($path, 'storage/') : $path;
    }

    // URL gambar yang bisa ditampilkan, null jika file tidak ditemukan
    public function getGambarUrlAttribute(): ?string
    {
        $relative = $this->gambarRelativePath();

        if (! $relative) {
            return null;
        }

        if (Storage::disk('public')->exists($relative)) {
            return asset('storage/' . $relative);
        }

        // Fallback: file lama yang disimpan langsung di folder public/
        if (is_file(public_path($relative))) {
            return asset($relative);
        }

        return null;
    }
}
