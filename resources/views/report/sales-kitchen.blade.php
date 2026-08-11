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
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="col-md d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fa fa-search"></i> Filter
                                </button>
                                <a href="{{ route('report.sales-kitchen') }}" class="btn btn-danger mr-2">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                                <button type="submit" formaction="{{ route('report.sales-kitchen.invoice') }}" formtarget="_blank" class="btn btn-warning mr-2">
                                    <i class="fa fa-print"></i> PDF
                                </button>
                                <button type="submit" formaction="{{ route('report.sales-kitchen.excel') }}" class="btn btn-success">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="text-muted">Menampilkan {{ $submissions->firstItem() ?? 0 }}–{{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} data</span>
                </div>
                <form method="GET" action="{{ route('report.sales-kitchen') }}" class="form-inline">
                    @foreach(request()->except('per_page', 'page') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <label class="mr-2 mb-0">Tampilkan</label>
                    <select name="per_page" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <label class="mb-0">data</label>
                </form>
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

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        var $btn = $(document.activeElement);
        if ($btn.attr('formaction') && $btn.attr('formaction').includes('excel')) {
            Swal.fire({
                title: 'Sedang Memproses Excel...',
                text: 'Mohon tunggu sebentar, file sedang dibuat.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            var downloadTimer = setInterval(function() {
                var token = getCookie("download_excel_completed");
                if (token !== undefined && token !== "") {
                    clearInterval(downloadTimer);
                    Swal.close();
                    document.cookie = "download_excel_completed=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                }
            }, 1000);
        }
    });

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
    }
});
</script>
@endsection