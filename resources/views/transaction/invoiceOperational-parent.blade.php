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
            max-width: 100%;
            margin: 0 auto;
            background: white;
            /* Padding dikurangi sedikit agar muat A4 margin default */
            padding: 10px; 
        }
        
        /* --- HEADER STYLE --- */
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header p {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }

        /* --- INFO SECTIONS (Diadaptasi ke Table untuk PDF) --- */
        .info-table {
            width: 100%;
            margin-bottom: 30px;
            border: none;
        }
        .info-table td {
            vertical-align: top;
            padding: 0;
            border: none; /* Hilangkan border default */
        }
        .info-box h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            display: inline-block; /* Agar garis bawah sesuai panjang teks */
            width: 100%;
        }
        .info-box p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
            line-height: 1.4;
        }

        /* --- DATA TABLE STYLE --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table thead {
            background: #333;
            color: white;
        }
        table.data-table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px; /* Disesuaikan sedikit agar muat */
        }
        table.data-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }
        /* table.data-table tbody tr:nth-child(even) {
            background: #f9f9f9; 
        } */

        /* --- UTILITY CLASSES --- */
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* --- TOTAL SECTION STYLE --- */
        .total-section {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        
        /* Menggunakan tabel untuk layout total agar rapi di PDF */
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 10px 0;
            border: none;
        }
        .grand-total-label {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            text-align: left;
        }
        .grand-total-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        /* --- FOOTER STYLE --- */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        
        {{-- HEADER --}}
        <div class="header">
            <h1>INVOICE REKAPITULASI</h1>
            <h1>BIAYA OPERASIONAL</h1>
            <p>Kode Pengajuan Utama: <strong>{{ $parent->kode }}</strong></p>
        </div>

        {{-- INFO SECTION (Menggunakan Table Layout untuk kompatibilitas PDF) --}}
        <table class="info-table">
            <tr>
                {{-- Kiri: Info Dapur --}}
                <td width="55%" style="padding-right: 20px;">
                    <div class="info-box">
                        <h3>Informasi Dapur</h3>
                        <p><strong>Nama Dapur:</strong> {{ $parent->kitchen->nama ?? '-' }}</p>
                        <p><strong>Alamat:</strong> {{ $parent->kitchen->alamat ?? '-' }}</p>
                        <p><strong>Total Supplier:</strong> {{ $parent->children->count() }} Supplier</p>
                    </div>
                </td>

                {{-- Kanan: Detail Pengajuan --}}
                <td width="45%">
                    <div class="info-box">
                        <h3>Detail Pengajuan</h3>
                        <p><strong>Tanggal:</strong> 
                            {{ \Carbon\Carbon::parse($parent->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
                        </p>
                        {{-- <p><strong>Tanggal Selesai:</strong> 
                            {{ $parent->updated_at ? \Carbon\Carbon::parse($parent->updated_at)->format('d F Y') : '-' }}
                        </p> --}}
                        <p><strong>Status:</strong> {{ strtoupper($parent->status) }}</p>
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