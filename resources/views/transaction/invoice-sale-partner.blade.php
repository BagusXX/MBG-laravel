<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Penjualan Bahan Baku Mitra - {{ $sales->first()->kode }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 800px;
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
            font-size: 28px;
            margin-bottom: 10px;
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
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        table tbody tr:hover {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
        }
        .total-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 10px;
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
            body {
                background: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
        @page {
            margin: 20mm;
        }
        .print-btn {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-btn button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .print-btn button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        {{-- <div class="print-btn no-print">
            <button onclick="window.print()">🖨️ Cetak Invoice</button>
        </div> --}}

        <div class="header">
            <h1>INVOICE PENJUALAN BAHAN BAKU MITRA</h1>
            <p>Kode Transaksi: <strong>{{ $sales->first()->kode }}</strong></p>
        </div>

        <div class="invoice-info">
            <div class="info-box">
                <h3>Informasi Penjualan</h3>
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($sales->first()->tanggal)->format('d F Y') }}</p>
                <p><strong>Dapur:</strong> {{ $sales->first()->kitchen->nama ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $sales->first()->kitchen->alamat ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h3>Informasi User</h3>
                <p><strong>Nama:</strong> {{ $sales->first()->user->name ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $sales->first()->user->email ?? '-' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Bahan Baku</th>
                    <th class="text-right">Jumlah</th>
                    <th>Satuan</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->bahanBaku->nama ?? '-' }}</td>
                    <td class="text-right">{{ number_format($sale->bobot_jumlah, 0, ',', '.') }}</td>
                    <td>{{ $sale->satuan->satuan ?? ($sale->bahanBaku && $sale->bahanBaku->unit ? $sale->bahanBaku->unit->satuan : '-') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->harga * $sale->bobot_jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row grand-total">
                <span>TOTAL HARGA:</span>
                <span>Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda</p>
            <p>Invoice ini dibuat secara otomatis oleh sistem</p>
        </div>
    </div>
</body>
</html>

