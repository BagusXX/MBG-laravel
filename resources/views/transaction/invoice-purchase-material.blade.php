<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembelian Bahan Baku - {{ $purchase->kode }}</title>
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        .layout-table {
        width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
        }

        .layout-table td {
            vertical-align: top; /* Pastikan teks mulai dari atas */
            padding: 0;
        }

        /* Helper untuk lebar kolom */
        .w-50 { width: 50%; }
        .w-33 { width: 33.33%; }

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
            <h1>INVOICE</h1>
            <h1>PEMBELIAN BAHAN BAKU</h1>
            <p>Kode Transaksi: <strong>{{ $purchase->first()->kode }}</strong></p>
        </div>

        <table class="layout-table">
            <tr>
            <td class="w-50">
                <div class="info-box">
                    <h3>Informasi Supplier</h3>
                    <p><strong>Nama Supplier:</strong> {{ $purchase->Supplier->nama }}</p>
                    <p><strong>Telepon:</strong> {{ $purchase->Supplier->nomor ?? '-' }}</p>
                    <p><strong>Alamat:</strong> {{ $purchase->Supplier->alamat ?? '-' }}</p>
                </div>
            </td>
            <td class="w-50 text-left">
                <div class="info-box">
                    <h3>Detail Transaksi</h3>
                    <p><strong>Tanggal Beli:</strong>
                        {{ \Carbon\Carbon::parse($purchase->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}</p>
                        <p><strong>Petugas:</strong> {{ $purchase->user->name ?? '-' }}</p>
                        <p><strong>Email:</strong> {{ $purchase->user->email ?? '-' }}</p>
                </div>
            </td>
            </tr>
        </table>        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Bahan Baku</th>
                    <th class="text-right">Jumlah</th>
                    <th>Satuan</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->bahanBaku->nama ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td>{{ $item->satuan->satuan ?? ($item->bahanBaku && $item->bahanBaku->satuan_id ? $item->bahanBaku->unit->satuan : '-') }}
                        </td>
                        <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- <div class="total-section"> --}}
            <div class="total-row grand-total">
                <span>TOTAL PEMBAYARAN:</span>
                <span>Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
            </div>
            {{-- </div> --}}
            
            <div class="footer">
                <p>Terima kasih atas kepercayaan Anda</p>
                <p>Invoice ini dibuat secara otomatis oleh sistem</p>
            </div>
    </div>
</body>

</html>
