@extends('adminlte::page')

@section('title', 'Laporan Pembelian Operasional')

@section('content_header')
    <h1>Laporan Pembelian Operasional</h1>
@endsection

@section('content')
    {{-- MAIN CARD --}}
    <div class="card">
        <div class="card-body">
            
            {{-- FILTER SECTION --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.purchase-operational') }}" method="GET">
                        <div class="row align-items-end">
                            
                            {{-- FILTER TANGGAL "DARI" --}}
                            <div class="col-md-3">
                                <label>Dari Tanggal</label>
                                <input type="date" name="from_date" class="form-control" 
                                       value="{{ request('from_date') }}">
                            </div>

                            {{-- FILTER TANGGAL "SAMPAI" --}}
                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="to_date" class="form-control" 
                                       value="{{ request('to_date') }}">
                            </div>

                            {{-- FILTER DAPUR --}}
                            <div class="col-md-3">
                                <label>Dapur</label>
                                <select name="kitchen_kode" class="form-control">
                                    <option value="">Semua Dapur</option>
                                    @foreach ($kitchens as $kitchen)
                                        <option value="{{ $kitchen->kode }}" 
                                            {{ request('kitchen_kode') == $kitchen->kode ? 'selected' : '' }}>
                                            {{ $kitchen->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- FILTER SUPPLIER --}}
                            {{-- Pastikan controller mengirim variabel $suppliers --}}
                            <div class="col-md-3">
                                <label>Supplier</label>
                                <select name="supplier_id" class="form-control">
                                    <option value="">Semua Supplier</option>
                                    @foreach ($suppliers ?? [] as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                            {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- TOMBOL FILTER --}}
                            <div class="col-md d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fa fa-search"></i> Filter
                                </button>
                                <a href="{{ route('report.purchase-operational') }}" class="btn btn-danger mr-2">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                                <button type="submit" 
                                        formaction="{{ route('report.purchase-operational.invoice') }}" 
                                        {{-- formtarget="_blank"  --}}
                                        class="btn btn-warning">
                                    <i class="fa fa-print"></i> Print
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE DATA --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="tableReport">
                    <thead >
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Tanggal</th>
                            {{-- <th>Kode Pengajuan</th> --}}
                            <th>Dapur</th>
                            <th>Supplier</th>
                            <th>Nama Barang</th>
                            <th>Keterangan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalGrand = 0; @endphp
                        
                        @forelse($reports as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->submission->tanggal)->format('d-m-Y') }}
                                </td>
                                {{-- <td>
                                    <span class="badge badge-light border">
                                        {{ $item->submission->kode ?? '-' }}
                                    </span>
                                </td> --}}
                                <td>{{ $item->submission->kitchen->nama ?? '-' }}</td>
                                <td>{{ $item->submission->supplier->nama ?? '-' }}</td>
                                <td>{{ $item->operational->nama ?? '-' }}</td>
                                <td>
                    {{ 
                        $item->keterangan ??
                        '-' 
                    }}
                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $totalGrand += $item->subtotal; @endphp
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    Data tidak ditemukan untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- FOOTER TOTAL --}}
                    @if($reports->count() > 0)
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="8" class="text-right">TOTAL KESELURUHAN</td>
                            <td class="text-right">Rp {{ number_format($totalGrand, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

        </div>
    </div>
@endsection

@section('js')
<script>
    // Opsional: Jika ingin menggunakan DataTables
    // $(document).ready(function() {
    //     $('#tableReport').DataTable();
    // });
</script>
@endsection