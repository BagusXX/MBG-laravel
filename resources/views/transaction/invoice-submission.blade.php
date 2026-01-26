<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pengajuan Menu - {{ $submission->kode }}</title>

    <style>
        /* --- STYLE DARI REFERENSI (SERAGAM) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #ffffff
            font-size: 14px;
        }

        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative; /* Untuk positioning tombol print */
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
            text-transform: uppercase;
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
            font-size: 15px;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }

        .info-box p, .info-box div {
            color: #666;
            font-size: 13px;
            margin: 3px 0;
            line-height: 1.4;
        }

        /* Table Styling */
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
            font-size: 13px;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left {text-align: left;}
        .text-muted { color: #888; }
        .font-italic { font-style: italic; }

        /* Total Section */
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }

        .total-row.grand-total {
            display: flex;
            justify-content: flex-end; /* Align right */
            gap: 50px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        /* Signature Section (Baru ditambahkan agar rapi) */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding: 0 50px;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-line {
            margin-top: 80px;
            border-bottom: 1px solid #333;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        /* Tombol Print Custom */
        .btn-print {
            position: absolute;
            top: 20px;
            right: 30px;
            background: #333;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-print:hover { background: #555; }

        /* CSS Print */
        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
            .btn-print { display: none; }
        }
    </style>
</head>

<body>

<div class="invoice-container">

    {{-- Tombol Print
    <button onclick="window.print()" class="btn-print no-print">
        Cetak Dokumen
    </button> --}}

    {{-- HEADER --}}
    <div class="header">
        <h1>Invoice</h1>
        <h1>Pengajuan Menu</h1>
        <p>Kode: {{ $submission->kode }}</p>
    </div>

    {{-- INFO SECTION (Dibagi 3 Kolom agar rapi) --}}
    <table class="layout-table">
        <tr>
            {{-- Kolom 1: Info Dapur --}}
            <td class="w-50">
                <div class="info-box">
                    <h3>Dipesan Oleh (Kitchen)</h3>
                    <p><strong>{{ $submission->kitchen->nama }}</strong></p>
                    <p>{{ $submission->kitchen->alamat ?? 'Alamat belum diisi' }}</p>
                    <p>Tgl PO: {{ \Carbon\Carbon::parse($submission->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}</p>
                </div>
            </td>

            {{-- Kolom 2: Info Supplier --}}
            <td class="w-50 text-left"> {{-- Tambah text-right jika ingin rata kanan, atau hapus jika ingin rata kiri --}}
                <div class="info-box">
                    <h3>Kepada (Supplier)</h3>
                    <p><strong>{{ $submission->supplier->nama }}</strong></p>
                    <p>{{ $submission->supplier->alamat ?? '-' }}</p>
                    <p>Telp: {{ $submission->supplier->kontak ?? '-' }}</p>
                </div>
            </td>
        </tr>
    </table>

        {{-- Kolom 3: Status
        <div class="info-box text-center">
            <h3>Status PO</h3>
            <div style="margin-top: 15px;">
                <span style="
                    border: 2px solid #333; 
                    color: #333; 
                    padding: 8px 20px; 
                    font-weight: bold; 
                    border-radius: 5px;
                    display: inline-block;
                    text-transform: uppercase;
                ">
                    {{ $submission->status }}
                </span>
            </div>
        </div> --}}

    </div>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Barang</th>
                <th width="10%" class="text-center">Jumlah</th>
                <th width="10%" class="text-center">Satuan</th>
                <th width="18%" class="text-right">Harga Satuan</th>
                <th width="18%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submission->details as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item->bahan_baku->nama }}
                    @if(!$item->recipe_bahan_baku_id) 
                        <br><small class="text-muted font-italic">(Item Tambahan)</small> 
                    @endif
                </td>
                <td class="text-center">{{ $item->qty_digunakan + 0 }}</td>
                <td class="text-center">{{ $item->bahanBaku->unit->satuan ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->harga_mitra ?? $item->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAL --}}
    <div class="total-section">
        <div class="total-row grand-total">
            <span>TOTAL TAGIHAN:</span>
            <span>Rp {{ number_format($submission->total_harga, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- TANDA TANGAN (MENGGUNAKAN TABLE JUGA AGAR TIDAK MENUMPUK)
    <table class="layout-table" style="margin-top: 50px;">
        <tr>
            <td class="text-center w-50">
                <p>Dibuat Oleh (Admin/Chef)</p>
                <div style="margin-top: 80px; border-bottom: 1px solid #333; width: 70%; margin-left: auto; margin-right: auto;"></div>
                <p style="margin-top: 5px;">( ...................................... )</p>
            </td>
            <td class="text-center w-50">
                <p>Disetujui Oleh (Supplier)</p>
                <div style="margin-top: 80px; border-bottom: 1px solid #333; width: 70%; margin-left: auto; margin-right: auto;"></div>
                <p style="margin-top: 5px;">( ...................................... )</p>
            </td>
        </tr>
    </table> --}}

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem.</p>
        <p>Terima kasih atas kerja samanya.</p>
    </div>

</div>

</body>
</html>