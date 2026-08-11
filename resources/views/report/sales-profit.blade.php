@extends('adminlte::page')

@section('title', 'Penjualan Bahan Baku')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Laporan Selisih Penjualan</h1>
@endsection

@section('content')
    <x-notification-pop-up />
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('report.sales-profit') }}" method="GET">
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
                        <a href="{{ route('report.sales-profit') }}" class="btn btn-danger mr-2">
                            <i class="fa fa-undo"></i> Reset
                        </a>
                        <button type="submit" formaction="{{ route('report.sales-profit.excel') }}" class="btn btn-success">
                            <i class="fa fa-file-excel"></i> Excel Rekap
                        </button>
                    </div>
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                </div>
            </form>
        </div>
    </div>
    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="text-muted">Menampilkan {{ $submissions->firstItem() ?? 0 }}–{{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} data</span>
                </div>
                <form method="GET" action="{{ route('report.sales-profit') }}" class="form-inline">
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
                        <!--<th>No</th>-->
                        <th>Kode</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th>PM (besar)</th>
                        <th>PM (kecil)</th>
                        <th>Supplier</th>
                        <th>Total Selisih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $index => $submission)
                        <tr>
                            <!--<td>{{ $index + 1 }}</td>-->
                            <td>{{ $submission->kode ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($submission->parentSubmission ? $submission->parentSubmission->tanggal : $submission->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                            <td>{{ $submission->kitchen ? $submission->kitchen->nama : '-' }}</td>
                            <td>{{ $submission->menu ? $submission->menu->nama : '-' }}</td>
                            <td>{{ $submission->porsi_besar ?? '-' }}</td>
                            <td>{{ $submission->porsi_kecil ?? '-' }}</td>
                            <td>{{ $submission->supplier ? $submission->supplier->nama : '-' }}</td>
                            <td>Rp{{ number_format($submission->details->sum('selisih'), 0, ',', '.') }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#modalDetailSales{{ $submission->id }}">
                                    Detail
                                </button>
                                <button type="button" class="btn btn-warning btn-sm btn-print-invoice"
                                    data-kode="{{ $submission->kode }}" window="_blank">
                                    <i class="fas fa-print mr-1"></i>Cetak
                                </button>
                                <button type="button" class="btn btn-success btn-sm btn-excel-invoice ml-1"
                                    data-kode="{{ $submission->kode }}">
                                    <i class="fas fa-file-excel mr-1"></i>Excel
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data penjualan bahan baku dari permintaan yang selesai
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    @foreach($submissions as $submission)
        <x-modal-detail id="modalDetailSales{{ $submission->id }}" size="modal-lg" title="Detail Penjualan Bahan Baku">
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
                                <td>: {{ $submission->supplier->nama }}</td>
                            </tr>
                            <tr>
                                <th class="py-1">Kontak</th>
                                <td>: {{ $submission->supplier->kontak }} - {{ $submission->supplier->nomor }}</td>
                            </tr>
                            {{-- <p class="text-muted small mb-0">Kontak: {{ $submission->supplier->kontak }} - {{
                                $submission->supplier->nomor }}</p> --}}
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
                                <th>Total Dapur</th>
                                <th>Total Mitra</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submission->details as $detail)
                                <tr>
                                    <td>{{ $detail->bahan_baku?->nama ?? '-' }}</td>
                                    <td>{{ number_format($detail->qty_digunakan, 2, ',', '.') }}</td>
                                    <td>{{ $detail->unit?->satuan ?? '-' }}</td>
                                    <td>Rp {{ number_format($detail->subtotal_dapur, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->subtotal_mitra, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->selisih, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Data bahan baku tidak ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total Selisih :</th>
                                <th>Rp{{ number_format($submission->details->sum('selisih'), 0, ',', '.') }}</th>
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
        $(document).ready(function () {
            // Handle tombol download invoice untuk sale-materials-kitchen
            $(document).on('click', '.btn-print-invoice', function (e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('BUTTON CLICKED');

                let kode = $(this).data('kode');
                console.log('KODE:', kode);

                if (!kode) {
                    console.error('Kode kosong');
                    return;
                }

                let url = "{{ route('report.sales-profit.printInvoice', ':kode') }}"
                    .replace(':kode', kode);
                url = url.replace(':kode', kode);

                console.log('OPEN URL:', url);

                window.open(url, '_blank');
            });

            // Handle tombol download Excel invoice
            $(document).on('click', '.btn-excel-invoice', function (e) {
                e.preventDefault();
                e.stopPropagation();

                let kode = $(this).data('kode');
                if (!kode) {
                    return;
                }

                let url = "{{ route('report.sales-profit.printInvoiceExcel', ':kode') }}";
                url = url.replace(':kode', kode);

                window.open(url, '_blank');
            });
        });
    </script>
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
@endpush