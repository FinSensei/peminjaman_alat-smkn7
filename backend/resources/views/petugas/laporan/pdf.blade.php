<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Peminjaman</title>

    <style>

        @page {
            size: A4 landscape;
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #eeeeee;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 7px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>
            LAPORAN TRANSAKSI PEMINJAMAN ALAT
        </h1>

        <p>
            Sistem Peminjaman Alat Laboratorium
        </p>

    </div>


    <div class="info">

        <strong>Filter Laporan:</strong>

        <br>

        Tanggal:
        {{ $startDate ?: 'Semua' }}
        s/d
        {{ $endDate ?: 'Semua' }}

        <br>

        Status:
        {{ $status ? ucfirst($status) : 'Semua Status' }}

    </div>


    <table>

        <thead>

            <tr>

                <th width="4%">
                    No
                </th>

                <th width="15%">
                    Peminjam
                </th>

                <th width="11%">
                    Tanggal Pinjam
                </th>

                <th width="11%">
                    Rencana Kembali
                </th>

                <th width="25%">
                    Alat
                </th>

                <th width="10%">
                    Status
                </th>

                <th width="11%">
                    Tanggal Kembali
                </th>

                <th width="13%">
                    Denda
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($peminjamans as $index => $peminjaman)

                <tr>

                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>


                    <td>
                        {{ $peminjaman->user->name ?? '-' }}
                    </td>


                    <td>
                        {{ $peminjaman->tgl_pinjam
                            ? \Carbon\Carbon::parse($peminjaman->tgl_pinjam)->format('d-m-Y')
                            : '-' }}
                    </td>


                    <td>
                        {{ $peminjaman->tgl_kembali_plan
                            ? \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->format('d-m-Y')
                            : '-' }}
                    </td>


                    <td>

                        @forelse($peminjaman->detailPinjam as $detail)

                            {{ $detail->alat->nama_alat ?? '-' }}
                            × {{ $detail->jumlah }}

                            @if(!$loop->last)
                                <br>
                            @endif

                        @empty

                            -

                        @endforelse

                    </td>


                    <td class="text-center">

                        {{ ucfirst($peminjaman->status) }}

                    </td>


                    <td>

                        @if($peminjaman->pengembalian)

                            {{ $peminjaman->pengembalian->tgl_kembali
                                ? \Carbon\Carbon::parse(
                                    $peminjaman->pengembalian->tgl_kembali
                                )->format('d-m-Y')
                                : '-' }}

                        @else

                            -

                        @endif

                    </td>


                    <td class="text-right">

                        Rp
                        {{ number_format(
                            $peminjaman->pengembalian->denda ?? 0,
                            0,
                            ',',
                            '.'
                        ) }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="8"
                        class="text-center"
                    >
                        Tidak ada data laporan.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Dicetak pada:
        {{ now()->format('d-m-Y H:i') }}

    </div>

</body>

</html>