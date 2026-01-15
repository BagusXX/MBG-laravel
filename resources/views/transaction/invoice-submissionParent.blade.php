<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Invoice - {{ $submission->kode }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body { font-size: 13px; color: #333; -webkit-print-color-adjust: exact; }
        .header-title { font-size: 20px; font-weight: bold; text-transform: uppercase; }
        .bg-gray { background-color: #f4f6f9 !important; }
        .table th { background-color: #e9ecef; border-color: #dee2e6; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; background: white; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            
            {{-- Tombol Print --}}
            <div class="text-right mb-3 no-print">
                <button onclick="window.print()" class="btn btn-primary btn-sm">
                    <i class="fas fa-print"></i> Cetak Rekapitulasi
                </button>
            </div>

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <h1 class="header-title text-primary">REKAPITULASI ORDER (MASTER)</h1>
                    <div class="font-weight-bold">Kode Pengajuan: {{ $submission->kode }}</div>
                </div>
                <div class="text-right">
                    <h5>{{ $submission->kitchen->nama }}</h5>
                    <small class="d-block">Tanggal: {{ \Carbon\Carbon::parse($submission->tanggal)->format('d F Y') }}</small>
                    <span class="badge badge-success px-3 py-1 mt-1">STATUS: SELESAI</span>
                </div>
            </div>

            {{-- LOOPING PER SUPPLIER (Split Order) --}}
            @forelse($submission->children as $child)
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-truck mr-1 text-muted"></i> 
                            Supplier: {{ $child->supplier->nama }}
                        </h6>
                        <small class="text-muted">No. PO: {{ $child->kode }}</small>
                    </div>

                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Nama Barang</th>
                                <th width="100" class="text-center">Qty</th>
                                <th width="80" class="text-center">Satuan</th>
                                <th width="150" class="text-right">Harga</th>
                                <th width="150" class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($child->details as $idx => $item)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $item->bahanBaku->nama }}</td>
                                <td class="text-center">{{ $item->qty_digunakan + 0 }}</td>
                                <td class="text-center">{{ $item->bahanBaku->unit->nama ?? '-' }}</td>
                                <td class="text-right">Rp {{ number_format($item->harga_mitra, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($item->subtotal_harga, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray">
                                <td colspan="5" class="text-right font-weight-bold">Total Supplier {{ $child->supplier->nama }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($child->total_harga, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="alert alert-warning text-center">
                    Belum ada data split order (PO Supplier).
                </div>
            @endforelse

            {{-- GRAND TOTAL --}}
            <div class="row mt-4 pt-3 border-top">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered">
                        <tr class="bg-primary text-white">
                            <td class="font-weight-bold text-right" style="font-size: 16px;">TOTAL KESELURUHAN (ALL SUPPLIERS)</td>
                            <td class="font-weight-bold text-right" style="font-size: 16px;">
                                {{-- Hitung ulang dari children agar akurat --}}
                                Rp {{ number_format($submission->children->sum('total_harga'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- TTD --}}
            <div class="row mt-5">
                <div class="col-4 text-center">
                    <p>Diajukan Oleh (Chef)</p>
                    <br><br>
                    <p>(..............................)</p>
                </div>
                <div class="col-4 text-center">
                    <p>Disetujui Oleh (Manager)</p>
                    <br><br>
                    <p>(..............................)</p>
                </div>
                <div class="col-4 text-center">
                    <p>Diketahui (Finance)</p>
                    <br><br>
                    <p>(..............................)</p>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>