<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice Pengajuan Menu - {{ $submission->kode }}</title>

    <style>
        /* --- STYLE DARI REFERENSI (SERAGAM) --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #ffffff font-size: 14px;
        }

        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            /* Untuk positioning tombol print */
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
            vertical-align: top;
            /* Pastikan teks mulai dari atas */
            padding: 0;
        }

        /* Helper untuk lebar kolom */
        .w-50 {
            width: 50%;
        }

        .w-33 {
            width: 33.33%;
        }

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

        .info-box p,
        .info-box div {
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
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-muted {
            color: #888;
        }

        .font-italic {
            font-style: italic;
        }

        /* Total Section */
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }

        .total-row.grand-total {
            display: flex;
            justify-content: flex-end;
            /* Align right */
            gap: 50px;
            font-size: 18px;
            font-weight: bold;
            text-align: right
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

        .btn-print:hover {
            background: #555;
        }

        /* CSS Print */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="invoice-container">
    <table class="layout-table" style="border-bottom: 3px double #000; margin-bottom: 20px;">
        <tr>
            <td style="width: 20%; text-align: center; vertical-align: top;">
                <img src="{{('icon_mbg.png') }}" alt="Logo BGN" style="height: 80px; width: 80px; object-fit: contain; margin-bottom: 20px;">
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle;">
                <!--<h2 style="margin: 0; text-transform: uppercase;">Koperasi Produsen</h2>-->
                <h2 style="margin: 0; text-transform: uppercase;">{{ $submission->supplier->nama ?? 'NAMA SUPPLIER' }}</h2>
                <p style="margin: 5px 0; font-size: 12px; line-height: 1.4;">
                    {{ $submission->supplier->alamat ?? '-' }}
                </p>
            </td>
            <td style="width: 20%; text-align: center; vertical-align: top;">
                @php
                    $relativePath = ltrim($submission->supplier->gambar, '/');
                    $logoPath = storage_path('app/public/' . $relativePath);
                @endphp
                
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}"
                         width="100"
                         style="object-fit: contain;">
                @endif
            </td>
        </tr>
    </table>

    {{-- INFO SECTION (Dibagi 3 Kolom agar rapi) --}}
    <table class="layout-table" style="margin-top: 10px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
        <tr>
            <td style="width: 35%; vertical-align: top;">
                <p style="font-weight: bold;  ">KEPADA :</p>
                <p style="margin: 0; text-transform: uppercase; font-size: 14px;">{{ $submission->kitchen->nama ?? '-' }}</p>
                <p style="margin: 0; font-size: 13px; color: #333;">{{ $submission->kitchen->alamat ?? '-' }}</p>
            </td>

            <td style="width: 30%; text-align: center; vertical-align: top;">
                <h1 style="font-size: 32px; font-style: italic; font-weight: bold; margin: 0; letter-spacing: 2px;">INVOICE</h1>
            </td>

            <td style="width: 35%; text-align: right; vertical-align: top;">
                <div style="margin-bottom: 10px;">
                    <p style="margin: 0; font-weight: bold; text-transform: uppercase;">TANGGAL :</p> 
                    <p style="margin: 0; font-size: 14px;">{{ \Carbon\Carbon::parse($submission->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                </div>
                @php
                    $romans = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
                    $monthIndex = (int)\Carbon\Carbon::parse($submission->tanggal)->format('m');
                    $romanMonth = $romans[$monthIndex];
                @endphp
                <div style="margin-bottom: 10px;">
                    <p style="margin: 0; font-weight: bold; text-transform: uppercase;">NO. INVOICE :</p> 
                    <p style="margin: 0; font-size: 13px;">{{ $submission->kode }}/INV/{{$romanMonth}}/{{ \Carbon\Carbon::parse($submission->tanggal)->format('Y') }}</p>
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

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Barang</th>
                <th width="10%" class="text-center">Jumlah</th>
                <th width="10%" class="text-center">Satuan</th>
                <th width="18%" class="text-right">Harga Satuan</th>
                <th width="18%" class="text-right">Subtotal Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submission->details as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->bahan_baku->nama }}
                        @if(!$item->recipe_bahan_baku_id)
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->cetak_qty, 2, ',', '.') }}</td>
                    <td class="text-center">{{ ucfirst($item->cetak_unit) }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_dapur ?? "-", 0, ',', '.') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($item->subtotal_dapur, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAL --}}
    

    <table class="table-footer" style="width: 100%; border-top: 2px solid #333; margin-top: 20px;">
        <tr>
            <td style="width: 30%;">
                <div style="font-size: 13px; line-height: 1.6;">
                    <p style="margin: 0; font-weight: bold;">PEMBAYARAN :</p>

                    @forelse($submission->supplier->bank_account as $bank)
                        <div style="margin-bottom: 10px;">
                            {{-- <p style="margin: 0;">{{ strtoupper($submission->supplier->nama) }}</p> --}}
                            <p style="margin: 0;">BANK {{ $bank->bank_name }}</p>
                            <p style="margin: 0;">A.N. {{ strtoupper( $bank->account_holder_name ?? $submission->supplier->nama) }}</p>
                            <p style="margin: 0;">No Rek. {{ $bank->account_number }}</p>
                        </div>
                    @empty
                        <p style="margin: 0; color: #888;">Data bank tidak tersedia</p>
                    @endforelse
                </div>
            </td>

            <td style="width: 50%; text-align: right; vertical-align: middle; padding-top: 10px">

                <div style="font-size: 18px; font-weight: bold; margin-bottom: 30px;">
                    TOTAL: Rp {{ number_format($submission->total_harga, 0, ',', '.') }}
                </div>
                
                <div style="display: inline-block; text-align: center; width: 200px;">
                    <p style="margin-bottom: 5px; font-size: 13px;">
                        {{ strtoupper($submission->kitchen->kota ?? '_____') }}, 
                        {{ \Carbon\Carbon::parse($submission->tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}
                    </p>
                    
                    {{-- Ruang Kosong untuk Tanda Tangan Manual --}}
                    <div style="height: 70px;"></div>

                    {{-- Nama Terang dengan Garis Bawah --}}
                    <p style="font-weight: bold; text-decoration: underline; margin: 0;">__________________
                    </p>
                    <p style="font-size: 11px; margin-top: 5px;">Nama Jelas & Tanda Tangan</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem.</p>
        <p>Terima kasih atas kerja samanya.</p>
    </div>

    </div>

</body>

</html>