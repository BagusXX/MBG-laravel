<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Rekapitulasi - {{ $parent->kode }}</title>

    {{-- ===== STYLE (SAMA PERSIS DENGAN INVOICE SATUAN) ===== --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        /* Helper untuk margin kanan pada box kiri */
        .info-box.left {
            margin-right: 20px;
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

        /* Style Khusus Header Supplier di dalam Loop */
        .supplier-section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            border-left: 5px solid #333;
            padding-left: 10px;
            background-color: #f9f9f9;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px; /* Jarak dikurangi karena ada subtotal per supplier */
        }

        table thead {
            background: #333;
            color: white;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        .text-right {
            text-align: right;
        }

        /* Subtotal per Supplier */
        .supplier-subtotal {
            text-align: right;
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            padding-right: 10px;
            font-style: italic;
        }

        .total-section {
            margin-top: 30px;
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
            .invoice-container { box-shadow: none; margin: 0; max-width: 100%; }
            .header, .invoice-info, table, .total-section { break-inside: avoid; }
        }
    </style>
</head>

<body> <div class="invoice-container">

    {{-- HEADER --}}
    <div class="header">
        <h1>INVOICE REKAPITULASI</h1>
        <h1>BIAYA OPERASIONAL</h1>
        <p>Kode Pengajuan Utama: <strong>{{ $parent->kode }}</strong></p>
    </div>

    {{-- INFO PARENT (DAPUR & TANGGAL) --}}
    <div class="invoice-info">
        <div class="info-box left">
            <h3>Informasi Dapur</h3>
            <p><strong>Nama Dapur:</strong> {{ $parent->kitchen->nama ?? '-' }}</p>
            <p><strong>Total Supplier:</strong> {{ $parent->children->count() }} Supplier</p>
        </div>

        <div class="info-box">
            <h3>Detail Pengajuan</h3>
            <p><strong>Tanggal Pengajuan:</strong>
                {{ \Carbon\Carbon::parse($parent->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
            </p>
            {{-- <p><strong>Tanggal Selesai:</strong>
                {{ $parent->tanggal_selesai ? \Carbon\Carbon::parse($parent->updated_at)->locale('id')->isoFormat('DD MMMM YYYY') : '-' }}
            </p> --}}
            <p><strong>Status:</strong> {{ strtoupper($parent->status) }}</p>
        </div>
    </div>

    {{-- LOOPING DATA PER SUPPLIER (CHILDREN) --}}
    @php $grandTotal = 0; @endphp

    @foreach ($parent->children as $child)
        
        {{-- JUDUL SUPPLIER --}}
        <div class="supplier-section-title">
            SUPPLIER: {{ strtoupper($child->supplier->nama ?? 'TANPA NAMA') }}
            <span style="float: right; font-size: 12px; font-weight: normal; margin-right: 10px;">
                Ref: {{ $child->kode }}
            </span>
        </div>

        {{-- TABEL ITEM SUPPLIER TERSEBUT --}}
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Operasional</th>
                    <th class="text-right" width="15%">Qty</th>
                    <th class="text-right" width="20%">Harga</th>
                    <th class="text-right" width="20%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($child->details as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $item->operational->nama ?? '-' }}
                            @if($item->keterangan)
                                <br><small style="color: #777;">({{ $item->keterangan }})</small>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- SUBTOTAL PER SUPPLIER --}}
        <div class="supplier-subtotal">
            Total Supplier Ini: <strong>Rp {{ number_format($child->total_harga, 0, ',', '.') }}</strong>
        </div>

        @php $grandTotal += $child->total_harga; @endphp

    @endforeach

    {{-- TOTAL KESELURUHAN (GRAND TOTAL) --}}
    <div class="total-section">
        <div class="total-row grand-total">
            <span>TOTAL PEMBAYARAN:</span>
            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Terima kasih atas kerja samanya</p>
        <p>Rekapitulasi Invoice ini dibuat secara otomatis oleh sistem</p>
    </div>

</div>

<script>
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>