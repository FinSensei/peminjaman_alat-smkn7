<?php

namespace App\Exports;

use App\Models\Peminjaman;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected $startDate = null,
        protected $endDate = null,
        protected $status = null,
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        $data = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('tgl_pinjam', [$this->startDate, $this->endDate]);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->latest()
            ->get();

        return $data->map(function ($p, $i) {
            return [
                'No' => $i + 1,
                'Peminjam' => $p->user->name ?? '-',
                'Tgl Pinjam' => $p->tgl_pinjam?->format('d-m-Y') ?? '-',
                'Rencana Kembali' => $p->tgl_kembali_plan?->format('d-m-Y') ?? '-',
                'Alat' => $p->detailPinjam->map(function ($d) {
                    return ($d->alat->nama_alat ?? '-') . ' x' . $d->jumlah;
                })->join(', ') ?: '-',
                'Status' => ucfirst($p->status),
                'Tgl Kembali' => $p->pengembalian?->tgl_kembali?->format('d-m-Y') ?? '-',
                'Denda' => $p->pengembalian->denda ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Peminjam', 'Tgl Pinjam', 'Rencana Kembali', 'Alat', 'Status', 'Tgl Kembali', 'Denda'];
    }
}
