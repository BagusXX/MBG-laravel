<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Rekapitulasi - {{ $parent->kode }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #fff; /* Background putih untuk PDF */
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
            /* margin-top: 20px; */
            padding-top: 10px;
            text-align: right
            /* border-top: 2px solid #333; */
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
            /* border-top: 1px solid #ddd; */
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .signature-wrapper {
            margin-top: 30px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-box {
            text-align: center;
            width: 33%; /* Sesuaikan lebar kolom tanda tangan */
        }

        .spacer {
            height: 80px; /* Ruang untuk stempel dan tanda tangan */
        }

        .table-footer {
            width: 100%;
            margin-top: 20px;
            border-top: 2px solid #333; 
            padding-top: 10px;
        }
        .table-footer td {
            border: none;
            vertical-align: top;
        }

        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <table class="layout-table" style="border-bottom: 3px double #000; margin-bottom: 20px;">
            <tr>
                <td style="width: 20%; text-align: center; vertical-align: top;">
                    {{-- Ganti path logo_bgn_mbg.png sesuai lokasi file Anda --}}
                    <img src="{{('icon_mbg.png') }}" alt="Logo BGN" style="height: 100px; width: 100px; object-fit: contain; margin-bottom: 20px;">
                </td>
                <td style="width: 60%; text-align: center; vertical-align: middle;">
                    <h2 style="margin: 0; text-transform: uppercase;">Rekapitulasi</h2>
                    <h2 style="margin: 0; text-transform: uppercase;">Biaya Operasional</h2>
                </td>
                <td style="width: 20%; text-align: center; vertical-align: top;">
                    <div style="height: 80px; width: 80px; display: inline-block;"></div>
                </td>
            </tr>
        </table>

        {{-- INFO SECTION (Menggunakan Table Layout untuk kompatibilitas PDF) --}}
        <table class="layout-table" style="margin-top: 10px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
            <tr>
                {{-- Kiri: Info Dapur --}}
                <td width="55%" style="padding-right: 20px;">
                     <div style="margin-bottom: 10px;">
                        <p style="margin: 0; font-weight: bold; text-transform: uppercase;">TANGGAL :</p> 
                        <p style="margin: 0; font-size: 14px;">{{ \Carbon\Carbon::parse($parent->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- TABEL DATA --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Operasional</th>
                    <th>Supplier</th>
                    <th class="text-center" width="10%"style="text-align: center;">Jumlah</th>
                    <th class="text-center" width="20%" style="text-align: center;">Harga</th>
                    {{-- <th class="text-center" width="20%" style="text-align: center;">Subtotal</th> --}}
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandTotal = 0; 
                    $no = 1; 
                @endphp

                @forelse ($parent->children as $child)
                    @foreach ($child->details as $item)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>
                                {{ $item->operational->nama ?? '-' }}
                                @if($item->keterangan)
                                    <br><small style="color: #777; font-style:italic;">({{ $item->keterangan }})</small>
                                @endif
                            </td>
                            <td>{{ $child->supplier->nama ?? 'Tanpa Nama' }}</td>
                            
                            <td class="text-center">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td class="text-center">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            {{-- <td class="text-center">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td> --}}
                        </tr>

                        @php $grandTotal += $item->harga_satuan; @endphp
                    @endforeach
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data operasional.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TOTAL SECTION --}}
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td class="grand-total-label">TOTAL PEMBAYARAN:</td>
                    <td class="grand-total-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <p>Terima kasih atas kerja samanya</p>
            <p>Rekapitulasi Invoice ini dibuat secara otomatis oleh sistem</p>
        </div>
    </div>
</body>
</html>