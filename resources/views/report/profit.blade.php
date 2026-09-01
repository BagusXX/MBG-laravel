@extends('adminlte::page')

@section('title', 'Laporan Selisih')

@section('content_header')
    <h1>Laporan Selisih Bahan Baku</h1>
@endsection

@section('content')
    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.profit') }}" method="GET">
                        <div class="row align-items-end">
                            {{-- FILTER TANGGAL "DARI" --}}
                            <div class="col-md-3">
                                <label>Dari</label>
                                <input type="date" name="from_date" class="form-control ">
                            </div>

                            {{-- FILTER MENU "SAMPAI"--}}
                            <div class="col-md-3">
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
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="col-md d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fa fa-search"></i> Filter
                                </button>
                                <a href="{{ route('report.profit') }}" class="btn btn-danger mr-2">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                                <button type="submit" formaction="{{ route('report.profit.invoice') }}" formtarget="_blank" class="btn btn-warning mr-2">
                                    <i class="fa fa-print"></i> PDF
                                </button>
                                <button type="submit" formaction="{{ route('report.profit.excel') }}" class="btn btn-success">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="text-muted">Menampilkan {{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }} data</span>
                </div>
                <form method="GET" action="{{ route('report.profit') }}" class="form-inline">
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
                        {{-- <th>Dapur</th> --}}
                        {{-- <th>Supplier</th> --}}
                        <th>Bahan Baku</th>
                        <th>Satuan</th>
                        <th>PM (besar)</th>
                        <th>PM (kecil)</th>
                        <th>Harga Dapur</th>
                        <th>Harga Mitra</th>
                        <th>Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->submission->parentSubmission->tanggal ?? $report->submission->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                            {{-- <td>{{ $report->submission->kitchen->nama}}</td>
                            <td>
                                @if ($report->submission->supplier_id)
                                    {{ optional($report->submission->supplier)->nama }}
                                @else
                                    -
                                @endif
                            </td> --}}
                            <td>
                                @if ($report->bahan_baku_id)
                                    {{ optional($report->bahan_baku)->nama }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $report->unit?->satuan ?? '-' }}</td>
                            <td>{{ $report->submission->porsi_besar }}</td>
                            <td>{{ $report->submission->porsi_kecil }}</td>
                            <td>Rp {{ number_format($report->subtotal_dapur, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($report->subtotal_mitra, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($report->selisih, 0, ',', '.') }}</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Data tidak ditemukan untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right"><strong>Total Selisih :</strong></td>
                        <td class="text-left"><strong>Rp{{ number_format($totalPageSubtotal, 0, '.', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex justify-content-end align-items-center mt-3">
                <div class="mt-3 d-flex justify-content-end">
                {{ $reports->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <x-modal-detail id="modalDetailSubmission" size="modal-lg" title="Detail Pengajuan Menu">
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