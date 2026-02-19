@extends('adminlte::page')

@section('title', 'Laporan Penjualan Dapur')

@section('content_header')
    <h1>Laporan Invoice Bahan Baku Dapur</h1>
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
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control ">
                            </div>
                            
                            {{-- FILTER "SAMPAI"--}}
                            <div class="col-md-2">
                                <label>Sampai</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control ">
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
                                {{-- <a href="{{ route('report.sales-kitchen.invoice', request()->all()) }}" class="btn btn-warning ml-2" target="_blank">
                                    <i class="fa fa-print"></i> Print
                                </a> --}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Dapur</th>
                        <th>Supplier</th>
                        <th>Bahan Baku</th>
                        <th>Qty</th>
                        <th width="50">Satuan</th>
                        <th>PM (besar)</th>
                        <th>PM (kecil)</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $item)
                    <tr>
                        <td>{{ $submissions->firstItem() + $loop->index }}</td>
                        {{-- Mengambil tanggal dari parentSubmission (disetujui) atau submission --}}
                        <td>{{ \Carbon\Carbon::parse($item->submission->parentSubmission ? $item->submission->parentSubmission->tanggal : $item->submission->tanggal)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>{{ $item->submission->kitchen->nama ?? '-' }}</td>
                        <td>{{ $item->submission->supplier->nama ?? '-' }}</td>
                        <td>{{ $item->bahan_baku->nama ?? '-' }}</td>
                        {{-- Menampilkan Qty asli tanpa konversi --}}
                        <td>{{ number_format($item->qty_digunakan, 0, ',', '.') }}</td>
                        <td>{{ $item->unit->satuan ?? '-' }}</td>
                        <td>{{ $item->submission->porsi_besar ?? '-' }}</td>
                        <td>{{ $item->submission->porsi_kecil ?? '-' }}</td>
                        <td>Rp{{ number_format($item->harga_dapur, 0, ',', '.') }}</td>
                        {{-- Menggunakan kolom subtotal_harga langsung --}}
                        <td>Rp{{ number_format($item->subtotal_dapur, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">Data tidak ditemukan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="10" class="text-right"><strong>Total :</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPageSubtotal, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex justify-content-end align-items-center mt-3">
                {{ $submissions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection