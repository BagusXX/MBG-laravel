@extends('adminlte::page')

@section('title', 'Laporan Penjualan Dapur')

@section('content_header')
    <h1>Laporan Penjualan Dapur</h1>
@endsection

@section('content')
    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
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

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Tanggal</th>
                        <th>Dapur</th>
                        <th>Supplier</th>
                        <th>Bahan Baku</th>
                        <th>Qty</th>
                        <th width="50">Satuan</th>
                        <th>Porsi</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report )
                    <tr>
                        <td>{{ $reports->firstItem() + $loop->index }}</td>
                        <td>{{ \Carbon\Carbon::parse($report->submission->tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $report->submission->kitchen->nama}}</td>
                        <td>
                            @if ($report->submission->supplier_id)
                                {{ optional($report->submission->supplier)->nama }}
                            @else-

                            @endif
                        </td>
                        <td>
                            @if ($report->bahan_baku_id)
                                {{ $report->bahan_baku->nama ?? '-' }}
                            @elseif ($report->recipeBahanBaku)
                                {{ $report->recipeBahanBaku->bahan_baku->nama ?? '-' }}
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $report->formatted_qty }}</td>
                        <td>{{ $report->display_unit }}</td>
                        <td>{{ $report->submission->porsi }}</td>

                        <td>Rp{{ number_format($report->harga_dapur, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format(($report->display_qty ?? 0) * ($report->harga_dapur ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Data tidak ditemukan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right"><strong>Total Harga :</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPageSubtotal, 0, '.', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="mt-3 d-flex justify-content-end">
                {{ $reports->links('pagination::bootstrap-4') }}
                </div>
                
            </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <x-modal-detail
        id="modalDetailSubmission"
        size="modal-lg"
        title="Detail Pengajuan Menu"
    >
        <div>
            <div>
                <p class="font-weight-bold mb-0">Tanggal:</p>
                <p>Rabu, 10 Desember 2025</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Nama Menu:</p>
                <p>Nasi Goreng</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Porsi:</p>
                <p>1000</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Dapur:</p>
                <p>Dapur A Tembalang</p>
            </div>
            <div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bawang Merah</td>
                            <td>10 kg</td>
                        </tr>
                        <tr>
                            <td>Bawang Putih</td>
                            <td>10 kg</td>
                        </tr>
                        <tr>
                            <td>Beras</td>
                            <td>100 kg</td>
                        </tr>
                        <tr>
                            <td>Kecap</td>
                            <td>15 L</td>
                        </tr>
                        <tr>
                            <td>Minyak Goreng</td>
                            <td>20 L</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal-detail>
@endsection