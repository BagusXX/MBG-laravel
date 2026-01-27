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
    <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.sales-kitchen') }}" method="GET">
                        <div class="row align-items-end">
                            {{-- FILTER TANGGAL "DARI" --}}
                            <div class="col-md-2">
                                <label>Dari</label>
                                <input type="date" name="from_date" class="form-control ">
                            </div>
                            
                            {{-- FILTER MENU "SAMPAI"--}}
                            <div class="col-md-2">
                                <label>Sampai</label>
                                <input type="date" name="to_date" class="form-control ">
                            </div>
                            
                            {{-- FILTER DAPUR --}}
                            <div class="col-md-3">
                                <label>Dapur</label>
                                <select name="kitchen_id" class="form-control">
                                    <option value="">Semua Dapur</option>
                                    @foreach ($kitchens as $kitchen)
                                    <option value="{{ $kitchen->id }}" {{ request('kitchen_id') == $kitchen->id ? 'selected' : '' }}>
                                        {{ $kitchen->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Supplier</label>
                                <select name="supplier_id" class="form-control">
                                    <option value="">Semua Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Bahan Baku</label>
                                <select name="bahan_baku_id" class="form-control select2">
                                    <option value="">Semua Bahan Baku</option>
                                    @foreach ($bahanBakus as $bahan)
                                        <option value="{{ $bahan->id }}" {{ request('bahan_baku_id') == $bahan->id ? 'selected' : '' }}>
                                            {{ $bahan->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fa fa-search"></i> Filter
                                </button>
                                <a href="{{ route('report.sales-kitchen') }}" class="btn btn-danger">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                                <a href="{{ route('report.sales-kitchen.invoice', request()->all()) }}" class="btn btn-warning ml-2" target="_blank">
                                    <i class="fa fa-print"></i> Print
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
                    @forelse($submissions as $index => $submission)
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
                                    window="_blank"
                                >
                                    <i class="fas fa-print mr-1"></i>Cetak
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
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%" class="py-1">Kode Permintaan</th>
                            <td>: {{ $submission->kode }}</td>
                        </tr>
                        <tr>
                            <th class="py-1">Tanggal</th>
                            <td>: {{ \Carbon\Carbon::parse($submission->tanggal)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th class="py-1">Dapur</th>
                            <td>: {{ $submission->kitchen->nama }}</td>
                        </tr>
                        <tr>
                            <th class="py-1">Menu</th>
                            <td>: {{ $submission->menu->nama }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="30%" class="py-1">Porsi</th>
                            <td>: {{$submission->porsi}}</td>
                        </tr>
                        @if($submission->supplier)
                            <tr>
                                <th class="py-1">Supplier</th>
                                <td>: {{ $submission->kitchen->nama }}</td>
                            </tr>
                            <tr>
                                <th class="py-1">Kontak</th>
                                <td>: {{ $submission->supplier->kontak }} - {{ $submission->supplier->nomor }}</td>
                            </tr>
                            {{-- <p class="text-muted small mb-0">Kontak: {{ $submission->supplier->kontak }} - {{ $submission->supplier->nomor }}</p> --}}
                        @endif
                    </table>
                </div>
            </div>
            <div>
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
                                    <td>{{ $detail->recipe?->bahan_baku?->nama ?? $detail->bahan_baku?->nama ?? '-' }}</td>
                                    <td>{{ number_format($detail->qty_digunakan, 2, ',', '.') }}</td>
                                    <td>{{ $detail->recipe?->bahan_baku?->unit?->satuan ?? $detail->bahan_baku?->unit?->satuan ?? '-' }}</td>
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
