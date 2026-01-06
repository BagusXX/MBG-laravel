@extends('adminlte::page')

@section('title', 'Penjualan Bahan Baku')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Penjualan Bahan Baku</h1>
@endsection

@section('content')
    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Permintaan</th>
                        <th>Tanggal</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th>Porsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $index => $submission)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $submission->kode ?? '-' }}</td>
                            <td>{{ $submission->tanggal ? \Carbon\Carbon::parse($submission->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $submission->kitchen ? $submission->kitchen->nama : '-' }}</td>
                            <td>{{ $submission->menu ? $submission->menu->nama : '-' }}</td>
                            <td>{{ $submission->porsi ?? '-' }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-toggle="modal"
                                    data-target="#modalDetailSales{{ $submission->id }}"
                                >
                                    Detail
                                </button>
                                <button 
                                    type="button"
                                    class="btn btn-warning btn-sm btn-download-invoice"
                                    data-kode="{{ $submission->kode }}"
                                >
                                    <i class="fas fa-print mr-1"></i>Cetak Invoice
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data penjualan bahan baku dari permintaan yang selesai</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    @foreach($submissions as $submission)
        <x-modal-detail
            id="modalDetailSales{{ $submission->id }}"
            size="modal-lg"
            title="Detail Penjualan Bahan Baku"
        >
            <div>
                <div class="mb-3">
                    <p class="font-weight-bold mb-0">Kode Permintaan:</p>
                    <p>{{ $submission->kode }}</p>
                </div>
                <div class="mb-3">
                    <p class="font-weight-bold mb-0">Tanggal:</p>
                    <p>{{ \Carbon\Carbon::parse($submission->tanggal)->format('d F Y') }}</p>
                </div>
                <div class="mb-3">
                    <p class="font-weight-bold mb-0">Dapur:</p>
                    <p>{{ $submission->kitchen->nama }}</p>
                </div>
                <div class="mb-3">
                    <p class="font-weight-bold mb-0">Menu:</p>
                    <p>{{ $submission->menu->nama }}</p>
                </div>
                <div class="mb-3">
                    <p class="font-weight-bold mb-0">Porsi:</p>
                    <p>{{ $submission->porsi }}</p>
                </div>
                <div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Qty Digunakan</th>
                                <th>Satuan</th>
                                <th>Harga Dapur</th>
                                <th>Subtotal Dapur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submission->details as $detail)
                                @php
                                    $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
                                    $subtotalDapur = $hargaDapur * $detail->qty_digunakan;
                                @endphp
                                <tr>
                                    <td>{{ $detail->recipe?->bahan_baku?->nama ?? $detail->bahanBaku?->nama ?? '-' }}</td>
                                    <td>{{ number_format($detail->qty_digunakan, 2, ',', '.') }}</td>
                                    <td>{{ $detail->recipe?->bahan_baku?->unit?->satuan ?? $detail->bahanBaku?->unit?->satuan ?? '-' }}</td>
                                    <td>Rp {{ number_format($hargaDapur, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($subtotalDapur, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Data bahan baku tidak ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-modal-detail>
    @endforeach
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Handle tombol download invoice untuk sale-materials-kitchen
            $(document).on('click', '.btn-download-invoice', function() {
                let kode = $(this).data('kode');
                
                // URL untuk download
                let downloadUrl = "{{ route('transaction.sale-materials-kitchen.invoice.download', ':kode') }}";
                downloadUrl = downloadUrl.replace(':kode', kode);
                
                // URL untuk preview (buka di tab baru)
                let previewUrl = "{{ route('transaction.sale-materials-kitchen.invoice', ':kode') }}";
                previewUrl = previewUrl.replace(':kode', kode);
                
                // Buat elemen link untuk download
                let downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.download = 'Invoice_' + kode + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                
                // Buka preview di tab baru setelah sedikit delay
                setTimeout(function() {
                    window.open(previewUrl, '_blank');
                }, 500);
            });
        });
    </script>
@endpush
