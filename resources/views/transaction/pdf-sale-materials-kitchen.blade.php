<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Invoice Dapur</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }
        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            margin-bottom: 20px;
        }
        .layout-table td {
            vertical-align: middle;
            padding-bottom: 10px;
        }
        .info-box {
            margin-bottom: 20px;
        }
        .info-box h3 {
            font-size: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
        }
        .info-box p {
            color: #555;
            margin: 2px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background: #333;
            color: white;
            padding: 6px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #ddd;
        }
        table.data-table td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        table.data-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table td {
            vertical-align: top;
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <table class="layout-table">
        <tr>
            <td style="width: 20%; text-align: left;">
                <img src="{{ public_path('icon_mbg.png') }}" alt="Logo BGN" style="height: 60px;">
            </td>
            <td style="width: 80%; text-align: center;">
                <h2 style="text-transform: uppercase;">Laporan Invoice Dapur</h2>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <h3>Informasi Laporan</h3>
        <p><strong>Total Transaksi:</strong> {{ $submissions->count() }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Tanggal Pengajuan</th>
                <th>Dapur</th>
                <th>Menu</th>
                <th>PM (besar)</th>
                <th>PM (kecil)</th>
                <th>Supplier</th>
                <th class="text-right">Total Dapur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($submissions as $index => $submission)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $submission->kode ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($submission->parentSubmission ? $submission->parentSubmission->tanggal : $submission->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ $submission->kitchen ? $submission->kitchen->nama : '-' }}</td>
                    <td>{{ $submission->menu ? $submission->menu->nama : '-' }}</td>
                    <td>{{ $submission->porsi_besar ?? '-' }}</td>
                    <td>{{ $submission->porsi_kecil ?? '-' }}</td>
                    <td>{{ $submission->supplier ? $submission->supplier->nama : '-' }}</td>
                    <td class="text-right">Rp{{ number_format($submission->details->sum('subtotal_dapur'), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #eee;">
                <td colspan="8" class="text-right">TOTAL:</td>
                <td class="text-right">Rp{{ number_format($totalPageSubtotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 65%;"></td>
            <td style="width: 35%; text-align: center;">
                <p style="margin-bottom: 50px;">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
                <p style="font-weight: bold; text-decoration: underline;">________________________</p>
                <p style="font-size: 11px;">Nama Jelas & Tanda Tangan</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem.</p>
    </div>
</div>
</body>
</html>
