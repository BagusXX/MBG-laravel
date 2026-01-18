<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian Dapur</title>

    {{-- ===== STYLE SAMA DENGAN PURCHASE ===== --}}
    <style>
        @page {
            size: landscape;
            /* margin: 1cm; */
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .invoice-container {
            max-width: 100%;
            /* margin: 0; */
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-box {
            flex: 1;
        }

        .info-box h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }

        .info-box p {
            color: #666;
            font-size: 14px;
            margin: 0px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
            /* padding: 3px */
        }

        table thead {
            background: #333;
            color: white;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        table th, table td {
            padding: 8px 4px;
            border-bottom: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }

        .total-row.grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; }
        }
    </style>
</head>

<div class="invoice-container">

    {{-- HEADER --}}
    <div class="header">
        <h1>LAPORAN</h1>
        <h1>PENJUALAN DAPUR</h1>
    </div>

    {{-- INFO --}}
    <div class="invoice-info">
        <div class="info-box">
            <h3>Informasi Laporan</h3>
            <p><strong>Total bahan Baku:</strong> {{ $reports->count() }}</p>
            <p><strong>Dicetak:</strong> {{ now()->locale('id')->isoFormat('DD MMMM YYYY') }}</p>
        </div>

        {{-- <div class="info-box">
            <h3>Detail Transaksi</h3>
            <p><strong>Tanggal:</strong>
                {{ \Carbon\Carbon::parse($submission->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
            </p>
            <p><strong>Dapur:</strong> {{ $submission->kitchen->nama ?? '-' }}</p>
            <p><strong>Status:</strong> {{ strtoupper($submission->status) }}</p>
        </div> --}}
    </div>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Bahan Baku</th>
                <th >Porsi</th>
                <th >Dapur</th>
                <th >Supplier</th>
                <th >Satuan</th>
                <th >Harga Dapur</th>
                <th >Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $index => $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->submission->tanggal)->locale('id')->isoFormat('DD MMM YYYY') }}</td>
                    <td>{{ $item->bahanBaku->nama ?? '-' }}</td>
                    <td >{{ number_format($item->submission->porsi, 0, ',', '.') }}</td>
                    <td>{{ $item->submission->kitchen->nama}}</td>
                    <td>
                        @if ($item->submission->supplier_id)
                                {{ optional($item->submission->supplier)->nama }}
                            @else-

                            @endif
                    </td>
                    <td >{{ $item->bahanBaku->unit->satuan ?? '-' }}</td>
                    <td >Rp{{ number_format($item->harga_dapur, 0, ',', '.') }}</td>
                    <td >Rp{{ number_format(($item->submission->porsi ?? 0) * ($item->harga_dapur ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAL --}}
    @if ($reports->count() > 0)
        <div class="total-section">
            <div class="total-row grand-total">
                <span>TOTAL KESELURUHAN:</span>
                <span>Rp {{ number_format($totalPageSubtotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Terima kasih atas kerja samanya</p>
        <p>Invoice ini dibuat secara otomatis oleh sistem</p>
    </div>

</div>
</body>
</html>
