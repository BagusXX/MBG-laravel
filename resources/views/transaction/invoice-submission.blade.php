<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $submission->kode }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body { font-size: 14px; color: #333; }
        .invoice-header { border-bottom: 2px solid #ddd; margin-bottom: 20px; padding-bottom: 10px; }
        .invoice-title { font-size: 24px; font-weight: bold; color: #000; }
        .table-invoice th { background-color: #f8f9fa; border-color: #dee2e6; }
        
        /* CSS Khusus Cetak */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
            .card { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body p-5">
            
            {{-- Tombol Print (Akan hilang saat diprint) --}}
            <div class="text-right mb-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Cetak Dokumen
                </button>
            </div>

            {{-- Header Invoice --}}
            <div class="row invoice-header">
                <div class="col-6">
                    <h1 class="invoice-title">PURCHASE ORDER (PO)</h1>
                    <p class="mb-0"><strong>Kode:</strong> {{ $submission->kode }}</p>
                    <p class="mb-0"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($submission->tanggal)->format('d F Y') }}</p>
                </div>
                <div class="col-6 text-right">
                    <h5>{{ $submission->kitchen->nama }}</h5>
                    <small>{{ $submission->kitchen->alamat ?? 'Alamat Dapur Belum Diisi' }}</small>
                </div>
            </div>

            {{-- Info Supplier --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong class="text-muted">KEPADA (SUPPLIER):</strong>
                    <h5 class="mt-1">{{ $submission->supplier->nama }}</h5>
                    <div>{{ $submission->supplier->alamat ?? '-' }}</div>
                    <div>Telp: {{ $submission->supplier->kontak ?? '-' }}</div>
                </div>
                <div class="col-md-6 text-right">
                    {{-- Status Stamp --}}
                    <div style="border: 2px solid #28a745; color: #28a745; display: inline-block; padding: 5px 15px; transform: rotate(-10deg); font-weight: bold; border-radius: 5px;">
                        {{ strtoupper($submission->status) }}
                    </div>
                </div>
            </div>

            {{-- Tabel Item --}}
            <table class="table table-bordered table-invoice">
                <thead>
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Nama Barang</th>
                        <th width="100" class="text-center">Qty</th>
                        <th width="80" class="text-center">Satuan</th>
                        <th width="150" class="text-right">Harga Satuan</th>
                        <th width="150" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submission->details as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            {{ $item->bahanBaku->nama }}
                            @if(!$item->recipe_bahan_baku_id) <br><small class="text-muted font-italic">(Item Tambahan)</small> @endif
                        </td>
                        <td class="text-center">{{ $item->qty_digunakan + 0 }}</td> {{-- +0 agar desimal .00 hilang --}}
                        <td class="text-center">{{ $item->bahanBaku->unit->nama ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->harga_mitra ?? $item->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal_harga, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right font-weight-bold">Total Tagihan</td>
                        <td class="text-right font-weight-bold bg-light">Rp {{ number_format($submission->total_harga, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Tanda Tangan --}}
            <div class="row mt-5">
                <div class="col-6 text-center">
                    <p>Dibuat Oleh (Admin/Chef)</p>
                    <br><br><br>
                    <p>(......................................)</p>
                </div>
                <div class="col-6 text-center">
                    <p>Disetujui Oleh (Supplier)</p>
                    <br><br><br>
                    <p>(......................................)</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Opsional: Otomatis print saat dibuka
    // window.print();
</script>
</body>
</html>