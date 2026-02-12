@extends('adminlte::page')

@section('title', 'Total Penjualan & Selisih')

@section('content_header')
    <h1>Total Penjualan & Selisih</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.sales-summary') }}" method="GET">
                        <div class="row align-items-end">
                            {{-- FILTER TANGGAL "DARI" --}}
                            <div class="col-md-4">
                                <label>Dari</label>
                                <input type="date" name="from_date" class="form-control ">
                            </div>
                            
                            {{-- FILTER MENU "SAMPAI"--}}
                            <div class="col-md-4">
                                <label>Sampai</label>
                                <input type="date" name="to_date" class="form-control ">
                            </div>
                            
                            {{-- FILTER DAPUR --}}
                            <div class="col-md-4">
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
                            <div class="col-md d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fa fa-search"></i> Filter
                                </button>   
                                <a href="{{ route('report.sales-summary') }}" class="btn btn-danger">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                                {{-- <a href="{{ route('report.sales-kitchen.invoice', request()->all()) }}"
                                    class="btn btn-warning ml-2" target="_blank">
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
                        <th width="13%">Kode</th>
                        <th width="24%">Total Invoice Dapur</th>
                        <th width="24%">Total Invoice Mitra</th>
                        <th>Selisih</th>
                        <th>85%</th>
                        <th>15%</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parents as $report )
                    <tr>
                        <td>{{ $report->kode }}</td>
                        <td>Rp{{ number_format($report->total_dapur, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->total_mitra, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->selisih, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->persen_85, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->persen_15, 0, ',', '.') }}</td>
                        <td>
                            {{-- TOMBOL TRIGGER --}}
                            <button class="btn btn-sm btn-info"
                                data-toggle="modal"
                                data-target="#modalDetailSales{{ $report->id }}">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Data tidak ditemukan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total :</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalSelisih, 0, '.', '.') }}</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPersen85, 0, '.', '.') }}</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPersen15, 0, '.', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $parents->links('pagination::bootstrap-4') }}
            </div>
        </div>
    
        @foreach ($parents as $report )
            <x-modal-detail
                id="modalDetailSales{{ $report->id }}" size="modal-xl" title="Detail Penjualan & Selisih">

                {{-- INFO PARENT --}}
                <div class="row mb-3 text-left"> {{-- Tambah text-left jika alignment aneh --}}
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="40%">Kode Parent</th>
                                <td>: {{ $report->kode }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td>: {{ \Carbon\Carbon::parse($report->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Dapur</th>
                                <td>: {{ $report->kitchen->nama }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- TABLE CHILD --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Supplier</th>
                                <th>Total Dapur</th>
                                <th>Total Mitra</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->children as $child)
                                @php
                                    $dapur = $child->details->sum('subtotal_dapur');
                                    $mitra = $child->details->sum('subtotal_mitra');
                                @endphp
                                <tr>
                                    <td>{{ $child->kode }}</td>
                                    <td>{{ $child->supplier->nama }}</td>
                                    <td>Rp{{ number_format($dapur,0,',','.') }}</td>
                                    <td>Rp{{ number_format($mitra,0,',','.') }}</td>
                                    <td>Rp{{ number_format($dapur - $mitra,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">TOTAL</th>
                                <th>Rp{{ number_format($report->total_dapur,0,',','.') }}</th>
                                <th>Rp{{ number_format($report->total_mitra,0,',','.') }}</th>
                                <th>Rp{{ number_format($report->selisih,0,',','.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-modal-detail>
            {{-- END MODAL --}}
        @endforeach
    </div>
@endsection