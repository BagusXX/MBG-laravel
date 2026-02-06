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
                        <th width="24%">Total Penjualan Dapur</th>
                        <th width="24%">Total Penjualan Mitra</th>
                        <th>Selisih</th>
                        <th>85%</th>
                        <th>15%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report )
                    <tr>
                        <td>{{ $report->kode }}</td>
                        <td>Rp{{ number_format($report->total_dapur, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->total_mitra, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->selisih, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->persen_85, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->persen_15, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Data tidak ditemukan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="9" class="text-right"><strong>Total :</strong></td>
                        {{-- <td class="text-left"><strong>Rp{{ number_format($totalPageSubtotal, 0, '.', '.') }}</strong></td> --}}
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection