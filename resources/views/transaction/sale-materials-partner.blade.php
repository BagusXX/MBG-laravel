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
                <form action="{{ route('transaction.sale-materials-partner.index') }}" method="GET">
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
                            <label>Menu</label>
                            <select name="menu_id" class="form-control select2">
                                <option value="">Semua Menu</option>
                                @foreach ($menus as $menu)
                                    <option value="{{ $menu->id }}" {{ request('menu_id') == $menu->id ? 'selected' : '' }}>
                                        {{ $menu->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <a href="{{ route('transaction.sale-materials-partner.index') }}" class="btn btn-danger">
                                <i class="fa fa-undo"></i> Reset
                            </a>
                            {{-- <a href="{{ route('transaction.sale-materials-partner.invoice', ['kode' => $item->kode])}}" class="btn btn-warning ml-2" target="_blank">
                                <i class="fa fa-print"></i> Print
                            </a> --}}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th>PM (besar)</th>
                        <th>PM (kecil)</th>
                        <th>Supplier</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $index => $submission)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $submission->kode ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($submission->parentSubmission ? $submission->parentSubmission->tanggal : $submission->tanggal)->locale('id')->translatedFormat('d F Y')}}</td>
                            <td>{{ $submission->kitchen ? $submission->kitchen->nama : '-' }}</td>
                            <td>{{ $submission->menu ? $submission->menu->nama : '-' }}</td>
                            <td>{{ $submission->porsi_besar ?? '-' }}</td>
                            <td>{{ $submission->porsi_kecil ?? '-' }}</td>
                            <td>{{ $submission->supplier ? $submission->supplier->nama : '-' }}</td>
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
                                    class="btn btn-warning btn-sm btn-print-invoice"
                                    data-kode="{{ $submission->kode }}"
                                >
                                    <i class="fas fa-print mr-1"></i>Cetak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data penjualan bahan baku dari permintaan yang selesai</td>
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
                            <th class="py-1">Tanggal Pengajuan</th>
                            <td>: {{ \Carbon\Carbon::parse($submission->parentSubmission ? $submission->parentSubmission->tanggal : $submission->tanggal)->locale('id')->translatedFormat('l, d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th class="py-1">Tanggal Digunakan</th>
                            <td>: {{ \Carbon\Carbon::parse($submission->parentSubmission->tanggal_digunakan)->locale('id')->translatedFormat('l, d-m-Y') }}</td>
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
                            <th width="30%" class="py-1">PM (besar)</th>
                            <td>: {{$submission->porsi_besar}}</td>
                        </tr>
                        <tr>
                            <th width="30%" class="py-1">PM (kecil)</th>
                            <td>: {{$submission->porsi_kecil}}</td>
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
                                <th>Harga Mitra</th>
                                <th>Subtotal Mitra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submission->details as $detail)
                                <tr>
                                    <td>{{ $detail->recipeBahanBaku?->bahan_baku?->nama ?? $detail->bahan_baku?->nama ?? '-' }}</td>
                                    <td>{{ number_format($detail->qty_digunakan ?? 0, 2, ',', '.') }}</td>
                                    <td>{{ $detail->unit?->satuan}}</td>
                                    <td>Rp {{ number_format($detail->harga_mitra ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->subtotal_mitra ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Data bahan baku tidak ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Total :</th>
                                <th>Rp{{ number_format($submission->details->sum('subtotal_mitra'), 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
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
            $(document).on('click', '.btn-print-invoice', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('BUTTON CLICKED');

                let kode = $(this).data('kode');
                console.log('KODE:', kode);

                if (!kode) {
                    console.error('Kode kosong');
                    return;
                }

                let url = "{{ route('transaction.sale-materials-partner.invoice', ':kode') }}"
                    .replace(':kode', kode);
                url = url.replace(':kode', kode);

                console.log('OPEN URL:', url);

                window.open(url, '_blank');
            });
        });
    </script>
@endpush
