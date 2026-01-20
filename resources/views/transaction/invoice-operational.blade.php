<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Operasional - {{ $submission->kode }}</title>

    {{-- ===== STYLE SAMA DENGAN PURCHASE ===== --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            /* background: #f5f5f5; */
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
        <h1>INVOICE</h1>
        <h1>BIAYA OPERASIONAL</h1>
        <p>Kode Transaksi: <strong>{{ $submission->kode }}</strong></p>
    </div>

    {{-- INFO --}}
    <table class="layout-table">
    <tr>
        <td class="w-50">
        <div class="info-box">
            <h3>Informasi Supplier</h3>
            <p><strong>Nama Supplier:</strong> {{ $submission->supplier->nama ?? '-' }}</p>
            <p><strong>Telepon:</strong> {{ $submission->supplier->nomor ?? '-' }}</p>
            <p><strong>Alamat:</strong> {{ $submission->supplier->alamat ?? '-' }}</p>
        </div>
        </td>

        <td class="w-50">
            <div class="info-box">
                <h3>Detail Operasional</h3>
                <p><strong>Tanggal:</strong>
                    {{ \Carbon\Carbon::parse($submission->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
                </p>
                <p><strong>Dapur:</strong> {{ $submission->kitchen->nama ?? '-' }}</p>
                <p><strong>Status:</strong> {{ strtoupper($submission->status) }}</p>
            </div>
        </td>
        </tr>
    </table>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Operasional</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($submission->details as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->operational->nama ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAL --}}
    <div class="total-section">
        <div class="total-row grand-total">
            <span>TOTAL PEMBAYARAN:</span>
            <span>Rp {{ number_format($submission->total_harga, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Terima kasih atas kerja samanya</p>
        <p>Invoice ini dibuat secara otomatis oleh sistem</p>
    </div>

</div>
</body>
</html>
