@extends('adminlte::page')

@section('title', 'Total Operasional')

@section('content_header')
    <h1>Total Operasional</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.total-operational') }}" method="GET">
                        <div class="row align-items-end">
                            {{-- FILTER TANGGAL "DARI" --}}
                            <div class="col-md-4">
                                <label>Dari</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            
                            {{-- FILTER TANGGAL "SAMPAI"--}}
                            <div class="col-md-4">
                                <label>Sampai</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            
                            {{-- FILTER DAPUR --}}
                            <div class="col-md-4">
                                <label>Dapur</label>
                                <select name="kitchen_kode" class="form-control">
                                    <option value="">Semua Dapur</option>
                                    @foreach ($kitchens as $kitchen)
                                        <option value="{{ $kitchen->kode }}" {{ request('kitchen_kode') == $kitchen->kode ? 'selected' : '' }}>
                                            {{ $kitchen->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fa fa-search"></i> Filter
                                </button>   
                                <a href="{{ route('report.total-operational') }}" class="btn btn-danger">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="10%">Kode</th>
                        @if(Auth::user()->hasAnyRole(['superadmin', 'operatorRegion']))
                        <th>Dapur</th>
                        @endif
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Total Pengajuan</th>
                        <th>98%</th>
                        <th>2%</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->kode }}</td>
                        @if(Auth::user()->hasAnyRole(['superadmin', 'operatorRegion']))
                        <td>{{ optional($report->kitchen)->nama ?? '-' }}</td>
                        @endif
                        <td>{{ \Carbon\Carbon::parse($report->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                        <td>{{ $report->keterangan }}</td>
                        <td>Rp{{ number_format($report->total_dapur, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->persen_98, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($report->persen_2, 0, ',', '.') }}</td>
                        <td>
                            <button class="btn btn-sm btn-info"
                                data-toggle="modal"
                                data-target="#modalDetailOperational{{ $report->id }}">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->hasAnyRole(['superadmin', 'operatorRegion']) ? 8 : 7 }}" class="text-center">Data tidak ditemukan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ Auth::user()->hasAnyRole(['superadmin', 'operatorRegion']) ? 4 : 3 }}" class="text-right"><strong>Total :</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($reports->getCollection()->sum('total_dapur'), 0, ',', '.') }}</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPersen98, 0, ',', '.') }}</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPersen2, 0, ',', '.') }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $reports->links('pagination::bootstrap-4') }}
            </div>
        </div>

        {{-- AREA MODAL CHILD --}}
        @foreach ($reports as $report)
            <x-modal-detail
                id="modalDetailOperational{{ $report->id }}" size="modal-xl" title="Detail Operasional">

                {{-- INFO PARENT --}}
                <div class="row mb-3 text-left">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="40%">Kode Pengajuan</th>
                                <td>: {{ $report->kode }}</td>
                            </tr>
                            <tr>
                                <th>Dapur</th>
                                <td>: {{ optional($report->kitchen)->nama ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td>: {{ \Carbon\Carbon::parse($report->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- TABLE CHILD DI DALAM MODAL --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal Faktur / PO</th>
                                <th>Supplier</th>
                                <th>Keterangan</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->children as $child)
                                <tr>
                                    <td>{{ $child->kode }}</td>
                                    <td>{{ \Carbon\Carbon::parse($child->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                                    <td>{{ optional($child->supplier)->nama ?? '-' }}</td>
                                    <td>{{ $child->keterangan }}</td>
                                    <td>Rp{{ number_format($child->total_harga, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">TOTAL</th>
                                <th>Rp{{ number_format($report->total_dapur, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-modal-detail>
        @endforeach
    </div>
@endsection
