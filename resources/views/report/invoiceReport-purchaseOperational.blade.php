<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian Operasional</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            /* background: #f5f5f5; */
        }

        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
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
            gap: 30px;
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
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px;
            font-size: 11pt; */
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
            /* vertical-align: top; */
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

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

<body>

<div class="invoice-container">

    {{-- HEADER --}}
    <div class="header">
        <h1>LAPORAN OPERASIONAL DAPUR</h1>
    </div>

    {{-- INFO --}}
    <div class="invoice-info">
        <div class="info-box">
            <h3>Informasi Laporan</h3>
            <p><strong>Total Transaksi:</strong> {{ $reports->count() }}</p>
            <p><strong>Dicetak:</strong> {{ now()->locale('id')->isoFormat('DD MMMM YYYY') }}</p>
        </div>
    </div>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="12%">Tanggal</th>
                <th width="15%">Dapur</th>
                <th width="10%">Supplier</th>
                <th width="15%">Barang</th>
                <th width="15%">Keterangan</th>
                <th width="4%" class="text-right">Jumlah</th>
                <th width="12%" class="text-right">Harga</th>
                <th width="12%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse ($reports as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->submission->tanggal)->locale('id')->isoFormat('DD MMM YYYY') }}</td>
                    <td>{{ $item->submission->kitchen->nama ?? '-' }}</td>
                    <td>
                        {{
                            $item->submission->supplier->nama
                            ?? $item->submission->parent->supplier->nama
                            ?? '-'
                        }}
                    </td>
                    <td>{{ $item->operational->nama ?? '-' }}</td>
                    <td>{{ $item->keterangan ?? $item->submission->keterangan ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $item->subtotal; @endphp
            @empty
                <tr>
                    <td colspan="9" class="text-center">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTAL --}}
    @if ($reports->count() > 0)
        <div class="total-section">
            <div class="total-row grand-total">
                <span>TOTAL KESELURUHAN:</span>
                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Invoice laporan ini dibuat secara otomatis oleh sistem</p>
        <p>Terima kasih atas kerja samanya</p>
    </div>

</div>

</body>
</html>
