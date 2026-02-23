@extends('adminlte::page')

@section('title', 'Persetujuan Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        /* Agar input number tidak ada panah spin up/down (opsional, biar rapi) */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .table-middle td {
            vertical-align: middle;
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Untuk Firefox */
        }

        .pagination {
            margin-bottom: 0;
        }

        /* Memperkecil ukuran pagination jika dirasa terlalu besar */
        .pagination-sm .page-link {
            padding: .25rem .5rem;
            font-size: .875rem;
        }
    </style>
@endsection

@section('content_header')
    <h1>Persetujuan Menu (Approval)</h1>
@endsection

@section('content')

    {{-- ALERT SUCCESS/ERROR --}}
    <x-notification-pop-up />

    {{-- FILTER SECTION --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Dapur</label>
                    <select id="filterKitchen" class="form-control">
                        <option value="">Semua Dapur</option>
                        @foreach($kitchens as $k)
                            <option value="{{ strtolower($k->nama) }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select id="filterStatus" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="diajukan">Diajukan</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        {{-- <option value="ditolak">Ditolak</option> --}}
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" id="filterDate" class="form-control">
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE DATA --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped" id="tableApproval">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th width="15%">Tanggal Pengajuan</th>
                        <th width="15%">Tanggal Digunakan</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th>PM Besar</th>
                        <th>PM Kecil</th>
                        {{-- <th>Total</th> --}}
                        <th>Status</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $item)
                                <tr data-kitchen="{{ strtolower($item->kitchen->nama ?? '') }}"
                                    data-status="{{ strtolower($item->status) }}"
                                    data-date="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}">
                                    <td>{{ $item->kode }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('l, d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_digunakan)->locale('id')->translatedFormat('l, d-m-Y') }}
                                    </td>
                                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                                    <td>{{ $item->menu ? $item->menu->nama : '-' }}</td>
                                    <td class="text-center">{{ $item->porsi_besar ?? 0 }}</td>
                                    <td class="text-center">{{ $item->porsi_kecil ?? 0 }}</td>
                                    {{-- Hitung Total Real-time dari Detail --}}
                                    {{-- @php
                                    $realTotal = $item->details->sum(function($detail) {
                                    // Logika prioritas harga: Mitra -> Dapur -> Satuan
                                    // Sesuaikan urutan ini dengan logika yang ada di Modal Anda
                                    $harga = $detail->harga_mitra ?? $detail->harga_dapur ?? $detail->harga_satuan ?? 0;
                                    return $detail->qty_digunakan * $harga;
                                    });
                                    @endphp
                                    <td>Rp {{ number_format($realTotal,2,',','.') }}</td> --}}
                                    <td>
                                        <span
                                            class="badge badge-{{
                        $item->status === 'selesai' ? 'success' :
                        ($item->status === 'diproses' ? 'info' : 'warning')
                                                                                                                                                                                                                                                        }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Tombol Review (Selalu Muncul) --}}
                                            <button class="btn btn-primary btn-proses" data-id="{{ $item->id }}"
                                                data-kitchen-id="{{ $item->kitchen_id }}" title="Detail / Review">
                                                Detail
                                            </button>

                                            {{-- Tombol Cetak Invoice (Hanya Muncul Jika Status SELESAI) --}}
                                            {{-- @if($item->status === 'selesai')
                                            <a href="{{ route('transaction.submission-approval.print-parent-invoice', $item->id) }}"
                                                target="_blank" class="btn btn-secondary" title="Cetak Rekap Invoice">
                                                <i class="fas fa-print"></i> Cetak Invoice
                                            </a>
                                            @endif --}}
                                        </div>
                                    </td>
                                </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $submissions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- =========================
    MODAL APPROVAL UTAMA
    ========================= --}}
    <div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                {{-- HEADER --}}
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Detail Pengajuan Bahan Baku </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body modal-body-scroll">

                    {{-- INFO & HEADER ACTIONS --}}
                    <div class=" row mb-3 pb-3">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm" style="width: 100%">
                                <tr>
                                    <th style="width: 35%; vertical-align: middle;">Kode</th>
                                    <td style="vertical-align: middle;">: <span id="modalTitleKode"></span></td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle;">Tanggal Pengajuan</th>
                                    <td style="vertical-align: middle;">: <span id="infoTanggal"></span></td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle;">Tanggal Digunakan</th>
                                    <td style="vertical-align: middle;">: <span id="infoTanggalDigunakan"></span></td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle;">Status</th>
                                    <td style="vertical-align: middle;">: <span id="infoStatusBadge"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table>
                                <tr>
                                    <th class="py-1">Menu</th>
                                    <td class="py-1">: <span id="infoMenu"></span></td>
                                </tr>
                                <tr>
                                    <th width="120" class="py-1">Dapur</th>
                                    <td class="py-1">: <span id="infoDapur"></span></td>
                                </tr>
                                <tr>
                                    <th class="py-1">PM Besar</th>
                                    <td class="py-1">: <span id="infoPmBesar"></span></td>
                                </tr>
                                <tr>
                                    <th class="py-1">PM kecil</th>
                                    <td class="py-1">: <span id="infoPmKecil"></span></td>
                                </tr>
                            </table>
                            <div id="wrapperActions" class="text-right mt-3">
                                {{-- Tombol Tolak (Muncul saat Diajukan) --}}
                                {{-- <button type="button" class="btn btn-danger d-none" id="btnTolakParent">
                                    <i class="fas fa-times mr-2"></i> Tolak
                                </button> --}}
                                {{-- Tombol Selesai (Muncul saat Diproses) --}}
                                @can('transaction.submission-approval.complete')
                                    <button type="button" class="btn btn-success btn-md d-none" id="btnSelesaiParent">
                                        <i class="fas fa-check-circle mr-2"></i> Selesaikan Pengajuan
                                    </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Actions --}}
                    </div>

                    {{-- PANEL SUPPLIER (SPLIT ORDER) --}}
                    {{-- <div id="panelSupplier" class="d-none mb-4 p-3 bg-white rounded border shadow-sm"> --}}
                        {{-- <div id="panelSupplier" class="d-flex align-items-end mb-2">
                            <div class="col-md-8">
                                <label class="font-weight-bold mb-1">Pilih Supplier untuk Barang Tercentang:</label>
                                <select id="selectSupplierSplit" class="form-control" required>
                                    <option value="">- Memuat data... -</option>
                                    @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary btn-block" id="btnSplitOrder">
                                    <i class="fas fa-paper-plane mr-1"></i> Proses Split Order
                                </button>
                            </div>
                        </div> --}}
                        <div id="panelSupplier" class="row align-items-end mb-3">
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-primary d-block mb-1">
                                        Pilih Supplier untuk Barang Tercentang:
                                    </label>

                                    <select id="selectSupplierSplit" class="form-control" {{-- style="width: 100%" --}}
                                        required>
                                        <option value="" selected disabled>- Pilih Supplier Khusus Dapur Ini -</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            @can('transaction.submission-approval.split')
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary btn-block action-only" id="btnSplitOrder">
                                        <i class="fas fa-paper-plane mr-1"></i>
                                        Proses Split Order
                                    </button>
                                </div>
                            @endcan
                        </div>

                        {{--
                    </div> --}}

                    {{-- TABEL RINCIAN --}}
                    <form id="formUpdateHarga">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="40" class="text-center action-only">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th>Bahan Baku/Bahan Masak</th>
                                        <th width="90" class="text-center">Qty</th>
                                        <th width="80" class="text-center">Satuan</th>
                                        {{-- DUA KOLOM HARGA DITAMPILKAN --}}
                                        <th width="130" class="text-center">Harga Satuan Dapur</th>
                                        <th width="130" class="text-center">Harga Satuan Mitra</th>
                                        <th width="140" class="text-right">Subtotal Dapur</th>
                                        <th width="140" class="text-right">Subtotal Mitra</th>
                                        @can('transaction.submission-approval.delete-detail')
                                            <th width="50" class="action-only"></th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody id="wrapperDetails">
                                    {{-- Inject JS --}}
                                </tbody>
                                {{-- <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">Total Keseluruhan</td>
                                        <td class="text-right font-weight-bold" id="infoTotal"></td>
                                        <td class="action-only"></td>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>

                        <div class="d-flex justify-content-between mt-2">
                            @can('transaction.submission-approval.add-bahan-baku')
                                <button type="button" class="btn btn-outline-secondary btn-sm action-only" id="btnTambahBahan">
                                    <i class="fas fa-plus mr-1"></i> Tambah Item Manual
                                </button>
                            @endcan
                            @can('transaction.submission-approval.update-harga')
                                <button type="submit" class="btn btn-sm btn-warning action-only" id="btnSimpanHarga">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan Harga/Qty
                                </button>
                            @endcan
                        </div>
                    </form>

                    {{-- RIWAYAT SPLIT ORDER --}}
                    <div id="sectionRiwayat" class="mt-4 pt-3 border-top">
                        <h6 class="font-weight-bold text-secondary mb-3">Riwayat Approval (Split Order)</h6>
                        <div id="wrapperRiwayat">
                            {{-- Inject JS --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH MANUAL --}}
    <x-modal-form id="modalAddBahanManual" title="Tambah Bahan Baku Manual" action="" submitText="Tambahkan">
        <div class="form-group">
            <label>Pilih Bahan Baku</label>
            <select id="selectBahanManual" class="form-control" style="width: 100%"></select>
        </div>
        {{-- INPUT FIELD SATUAN BARU --}}
        <div class="form-group">
            <label>Satuan</label>
            <select id="satuanBahanManualId" class="form-control select2" style="width: 100%">
                <option value="">- Pilih Satuan -</option>
                @foreach($units as $u)
                    <option value="{{ $u->id }}">{{ $u->satuan }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Jumlah (Qty)</label>
            <input type="number" id="qtyBahanManual" class="form-control" step="0.0001" min="0.0001">
        </div>
    </x-modal-form>

    {{-- FORM HIDDEN STATUS --}}
    <form id="formUpdateStatus" method="POST" style="display:none;">
        @csrf @method('PATCH')
        <input type="hidden" name="status" id="inputStatusFinal">
    </form>

@endsection

@section('js')
    @include('components.modal-confirm')
    @include('components.notification-pop-up-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        let currentSubmissionId = null;
        let currentKitchenId = null;
        let isReadonlyStatus = false;

        const formatRupiah = (num) => 'Rp ' + parseFloat(num).toLocaleString('id-ID', { minimumFractionDigits: 0 });
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right" };

        const formatQty = (number) => {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 4 // Toleransi desimal lebih banyak
            }).format(number);
        };

        $(document).ready(function () {

            $('#selectBahanManual').select2({ dropdownParent: $('#modalAddBahanManual') });
            $('#satuanBahanManualId').select2({ dropdownParent: $('#modalAddBahanManual') });

            // Prevent scroll number input
            $('form').on('wheel', 'input[type=number]', function (e) {
                $(this).blur();
            });

            $(document).on('hidden.bs.modal', '.modal', function () {
                // Cek apakah masih ada modal lain yang terbuka
                if ($('.modal:visible').length) {
                    $('body').addClass('modal-open');
                }
            });

            // --- BUKA MODAL ---
            $('.btn-proses').on('click', function () {
                currentSubmissionId = $(this).data('id');
                currentKitchenId = $(this).data('kitchen-id');

                // Panggil fungsi utama (Single Source of Truth)
                loadAllData();

                $('#modalApproval').modal('show');
            });

            // --- FUNGSI UTAMA LOAD DATA (GABUNGAN HEADER, HISTORY & DETAIL) ---
            function loadAllData() {
                // Gunakan endpoint yang sudah diperbaiki di Controller
                $.get("{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/data", function (data) {

                    // 1. ISI HEADER
                    $('#modalTitleKode').text(data.kode);
                    $('#infoTanggal').text(data.tanggal);
                    $('#infoTanggalDigunakan').text(data.tanggal_digunakan);
                    $('#infoMenu').text(data.menu);
                    $('#infoPmBesar').text(data.porsi_besar || 0);
                    $('#infoPmKecil').text(data.porsi_kecil || 0);
                    $('#infoDapur').text(data.kitchen);

                    let badgeClass = data.status === 'diproses' ? 'info' : (data.status === 'selesai' ? 'success' : 'warning');
                    $('#infoStatusBadge').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);

                    isReadonlyStatus = (data.status === 'selesai');

                    // 2. RESET TOMBOL & MODE
                    $('#btnTolakParent, #btnSelesaiParent, #panelSupplier').addClass('d-none');
                    $('.action-only').removeClass('d-none');
                    setReadonlyMode(false);

                    if (data.status === 'diajukan') {
                        $('#btnTolakParent').removeClass('d-none');
                        $('#panelSupplier').removeClass('d-none');
                    } else if (data.status === 'diproses') {
                        $('#btnSelesaiParent, #panelSupplier').removeClass('d-none');
                    } else if (data.status === 'selesai') {
                        $('.action-only').addClass('d-none');
                        setReadonlyMode(true);
                    }

                    // 3. RENDER SUPPLIER DROPDOWN
                    let supplierOpts = '<option value="">- Pilih Supplier Khusus Dapur Ini -</option>';
                    if (data.suppliers && data.suppliers.length > 0) {
                        data.suppliers.forEach(s => {
                            supplierOpts += `<option value="${s.id}">${s.nama}</option>`;
                        });
                    } else {
                        supplierOpts = '<option value="" disabled>Tidak ada supplier untuk dapur ini</option>';
                    }
                    $('#selectSupplierSplit').html(supplierOpts);

                    // 4. RENDER RIWAYAT SPLIT ORDER
                    renderHistory(data.history);

                    // 5. RENDER TABEL DETAIL BAHAN BAKU (PENTING: Gunakan data.details langsung)
                    renderDetailsTable(data.details);

                }).fail(function () {
                    showNotificationPopUp('error', 'Gagal memuat data pengajuan.', 'Error');
                });
            }

            // --- FUNGSI RENDER HISTORY ---
            function renderHistory(historyData) {
                let historyHtml = '';
                if (historyData && historyData.length > 0) {
                    historyData.forEach(h => {
                        let invoiceUrl = "{{ url('dashboard/transaksi/approval-menu') }}/" + h.id + "/invoice";

                        // Render Items per Child
                        let itemsHtml = '';
                        if (h.items && h.items.length > 0) {
                            h.items.forEach(item => {
                                // Sesuaikan key dengan controller (qty, satuan, harga)
                                itemsHtml += `
                                                                                        <li>
                                                                                            ${item.nama}
                                                                                            <span class="text-muted small">(${formatQty(item.qty)} ${item.unit} x ${formatRupiah(item.harga_dapur)})</span>
                                                                                        </li>
                                                                                    `;
                            });
                        } else {
                            itemsHtml = `<li class="text-muted font-italic small">Tidak ada item</li>`;
                        }

                        historyHtml += `
                                            <div class="card mb-2 border">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong class="text-dark">${h.kode}</strong> 
                                                            <span class="text-muted mx-2">|</span> 
                                                            <i class="fas fa-truck mr-1 text-secondary"></i> ${h.supplier_nama}
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-end">
                                                            <span class="badge badge-success mr-3 px-2 py-1">DISETUJUI</span>
                                                            <strong class="mr-3 text-dark">${formatRupiah(h.total)}</strong>

                                                            @can('transaction.submission-approval.delete-detail')
                                                                <button class="btn btn-sm btn-outline-danger btn-delete-child action-only" 
                                                                        data-id="${h.id}" title="Hapus Split Order">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            @endcan
                                                        </div>


                                                    </div>
                                                    <ul class="mb-0 pl-3" style="font-size: 0.9em; list-style-type: disc;">
                                                        ${itemsHtml}
                                                    </ul>
                                                    <div  class="d-flex justify-content-between align-items-center mt-2 mb-1 border-top pt-2"">
                                                        <span class="text-muted medium">
                                                            Dicetak Pada : 
                                                            ${h.created_at}
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-print-invoice" data-id="${h.id}">
                                                            <i class="fas fa-print mr-1"></i> Cetak Invoice 
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>`;
                    });
                } else {
                    historyHtml = '<div class="text-muted font-italic text-center py-2 border bg-light rounded">Belum ada riwayat split order.</div>';
                }
                $('#wrapperRiwayat').html(historyHtml);
            }

            $(document).on('click', '.btn-print-invoice', function () {
                let id = $(this).data('id');
                window.open(
                    "{{ url('dashboard/transaksi/approval-menu') }}/" + id + "/invoice",
                    "_blank"
                );
            });


            // --- FUNGSI RENDER TABEL DETAIL (Menggantikan loadDetails) ---
            function renderDetailsTable(detailsData) {
                let html = '';
                // let grandTotal = 0;

                if (detailsData && detailsData.length > 0) {
                    detailsData.forEach(item => {
                        // Pastikan nilai angka aman
                        let qty = parseFloat(item.qty_digunakan) || 0;
                        let hrgDapur = parseFloat(item.harga_dapur) || 0;
                        let hrgMitra = parseFloat(item.harga_mitra) || 0;

                        // Subtotal dari server (atau hitung ulang via JS juga boleh)
                        let subDapur = parseFloat(item.subtotal_dapur) || (qty * hrgDapur);
                        let subMitra = parseFloat(item.subtotal_mitra) || (qty * hrgMitra);

                        // Manual Label Logic
                        let manualLabel = ''; // Sesuaikan jika ada logic manual

                        html += `
                                                                            <tr>
                                                                                <td class="text-center align-middle action-only">
                                                                                    <input type="checkbox" class="check-item" value="${item.id}">
                                                                                </td>
                                                                                <td class="align-middle">
                                                                                    <span class="text-dark font-weight-bold">${item.bahan_baku_nama || item.nama_bahan}</span>
                                                                                    ${manualLabel}
                                                                                    <input type="hidden" name="details[${item.id}][id]" value="${item.id}">
                                                                                    <input type="hidden" name="details[${item.id}][satuan_id]" value="${item.satuan_id}">
                                                                                </td>

                                                                                {{-- QTY --}}
                                                                                <td class="align-middle px-1">
                                                                                    <input type="number" step="0.0001" class="form-control form-control-sm text-center bg-light input-hitung input-qty" 
                                                                                        name="details[${item.id}][qty_digunakan]" value="${item.qty_digunakan}">
                                                                                </td>

                                                                                {{-- SATUAN --}}
                                                                                <td class="text-center align-middle">
                                                                                    <span class="badge badge-light border">${item.nama_satuan}</span>
                                                                                </td>

                                                                                {{-- HARGA DAPUR (SATUAN) --}}
                                                                                <td class="align-middle px-1">
                                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-right input-hitung input-harga-dapur" 
                                                                                        name="details[${item.id}][harga_dapur]" 
                                                                                        value="${hrgDapur}" placeholder="0"> 
                                                                                </td>

                                                                                {{-- HARGA MITRA (SATUAN) --}}
                                                                                <td class="align-middle px-1">
                                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-right border-info input-hitung input-harga-mitra" 
                                                                                        name="details[${item.id}][harga_mitra]" 
                                                                                        value="${hrgMitra}" placeholder="0"> </td>

                                                                                {{-- SUBTOTAL DAPUR (READONLY) --}}
                                                                                <td class="align-middle px-1">
                                                                                    <input type="text" class="form-control form-control-sm text-right bg-light text-bold subtotal-dapur" 
                                                                                        readonly value="${formatRupiahInput(subDapur)}"> </td>

                                                                                {{-- SUBTOTAL MITRA (READONLY) --}}
                                                                                <td class="align-middle px-1">
                                                                                    <input type="text" class="form-control form-control-sm text-right bg-light text-bold subtotal-mitra" 
                                                                                        readonly value="${formatRupiahInput(subMitra)}"> </td>

                                                                                        @can('transaction.submission-approval.delete-detail')
                                                                                            <td class="text-center align-middle action-only">
                                                                                                    <button type="button" class="btn btn-link text-danger btn-delete-detail" data-id="${item.id}">
                                                                                                        <i class="fas fa-trash-alt"></i>
                                                                                                    </button>
                                                                                                    </td>
                                                                                        @endcan
                                                                            </tr>
                                                                        `;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada item bahan baku.</td></tr>';
                }

                $('#wrapperDetails').html(html);
                // $('#infoTotal').text(formatRupiah(grandTotal)); // Aktifkan jika ada elemen infoTotal

                if (isReadonlyStatus) {
                    setReadonlyMode(true);
                }
            }

            const formatRupiahInput = (num) => {
                return parseFloat(num).toLocaleString('id-ID', { minimumFractionDigits: 0 });
            };

            // --- 3. LOGIKA HITUNG OTOMATIS (LIVE CALCULATION) ---
            $(document).on('input', '.input-hitung', function () {
                let row = $(this).closest('tr');

                // Ambil nilai
                let qty = parseFloat(row.find('.input-qty').val()) || 0;
                let hargaDapur = parseFloat(row.find('.input-harga-dapur').val()) || 0;
                let hargaMitra = parseFloat(row.find('.input-harga-mitra').val()) || 0;

                // Hitung
                let subDapur = qty * hargaDapur;
                let subMitra = qty * hargaMitra;

                // Tampilkan (Formatted)
                row.find('.subtotal-dapur').val(formatRupiahInput(subDapur));
                row.find('.subtotal-mitra').val(formatRupiahInput(subMitra));
            });

            // --- HAPUS SPLIT ORDER ---
            $(document).on('click', '.btn-delete-child', function () {
                let btn = $(this);
                let childId = btn.data('id');

                confirmAction({
                    type: 'delete',
                    title: 'Konfirmasi Hapus',
                    message: `Yakin ingin menghapus split order ini?`,
                    confirmText: 'Hapus',
                    onConfirm: function () {
                        let originalContent = btn.html();
                        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

                        $.ajax({
                            url: "{{ url('dashboard/transaksi/approval-menu/child') }}/" + childId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                showNotificationPopUp('success', 'Split order berhasil dihapus.', 'Berhasil');
                                loadAllData(); // REFRESH DATA
                            },
                            error: function (xhr) {
                                showNotificationPopUp('error', xhr.responseJSON?.message ?? 'Gagal menghapus data.', 'Error');
                                btn.html(originalContent).prop('disabled', false);
                            }
                        });
                    }
                });
            });

            // --- SPLIT ORDER ---
            $('#btnSplitOrder').on('click', function () {
                let supplierId = $('#selectSupplierSplit').val();
                let selectedIds = [];
                $('.check-item:checked').each(function () { selectedIds.push($(this).val()); });

                if (!supplierId) { showNotificationPopUp('warning', 'Harap pilih supplier!'); return; }
                if (selectedIds.length === 0) { showNotificationPopUp('warning', 'Harap centang minimal satu barang!'); return; }

                confirmAction({
                    title: 'Konfirmasi Split Order',
                    message: `Yakin ingin memproses ${selectedIds.length} item ke supplier ini?`,
                    confirmText: 'Proses',
                    onConfirm: function () {
                        $.ajax({
                            url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/split",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                supplier_id: supplierId,
                                selected_details: selectedIds
                            },
                            success: function (res) {
                                showNotificationPopUp('success', 'Order berhasil dipisah.', 'Berhasil');
                                // --- TAMBAHKAN INI UNTUK MENGATASI STUCK ---
                                // 1. Pastikan backdrop modal konfirmasi benar-benar hilang
                                $('.modal-backdrop').remove();

                                // 2. Paksa class modal-open tetap ada di body agar modal utama bisa di-scroll
                                $('body').addClass('modal-open').css('overflow', 'auto');

                                $('#selectSupplierSplit').val('').trigger('change');
                                $('#checkAll').prop('checked', false);

                                loadAllData(); // REFRESH DATA
                            },
                            error: function (xhr) {
                                showNotificationPopUp('error', xhr.responseJSON?.message ?? 'Gagal memproses.', 'Error');

                                // Jika error pun tetap stuck, pastikan scroll dikembalikan
                                $('body').addClass('modal-open');
                            }
                        });
                    }
                });
            });

            // --- SIMPAN HARGA ---
            $('#formUpdateHarga').on('submit', function (e) {
                e.preventDefault();
                let btn = $('#btnSimpanHarga');
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

                let details = [];

                $('#wrapperDetails tr').each(function () {
                    let row = $(this);

                    let id = row.find('input[name*="[id]"]').val();
                    if (!id) return;

                    details.push({
                        id: id,
                        qty_digunakan: toNumber(row.find(`input[name="details[${id}][qty_digunakan]"]`).val()),
                        satuan_id: row.find(`input[name="details[${id}][satuan_id]"]`).val(),
                        harga_dapur: toNumber(row.find(`input[name="details[${id}][harga_dapur]"]`).val()),
                        harga_mitra: toNumber(row.find(`input[name="details[${id}][harga_mitra]"]`).val()),
                    });
                });

                console.log(details);

                $.ajax({
                    url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/update-harga",
                    type: 'PATCH',
                    data: {
                        _token: "{{ csrf_token() }}",
                        details: details
                    },
                    success: function (response) {
                        showNotificationPopUp('success', response.message || 'Data berhasil diperbarui', 'Berhasil');
                        loadAllData();
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        showNotificationPopUp('error', xhr.responseJSON?.message ?? 'Gagal menyimpan perubahan.', 'Error');
                    },
                    complete: function () {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // --- TAMBAH MANUAL ---
            $('#btnTambahBahan').click(function () {
                let url = "{{ route('transaction.submission-approval.helper.bahan-baku', ['kitchen' => 'FAKE_ID']) }}".replace('FAKE_ID', currentKitchenId);
                $('#selectBahanManual').empty().append('<option>Loading...</option>');

                $.get(url, function (data) {
                    let opts = '<option value="">Pilih Bahan</option>';
                    data.forEach(b => {
                        // Kita simpan nama satuan di 'data-unit-nama' agar bisa diambil saat change
                        let unitNama = b.unit?.satuan || '-';
                        opts += `<option value="${b.id}" data-satuan="${b.satuan_id}" data-unit-nama="${unitNama}">${b.nama}</option>`;
                    });
                    $('#selectBahanManual').html(opts);
                    $('#modalAddBahanManual').modal('show');
                });
            });

            // Tambahkan listener ketika pilihan bahan berubah
            $('#selectBahanManual').on('change', function () {
                let selected = $(this).find(':selected');
                let satuanId = selected.data('satuan');
                let unitNama = selected.data('unit-nama'); // Kita akan tambahkan atribut ini nanti

                if (satuanId) {
                    $('#satuanBahanManualId').val(satuanId).trigger('change');
                    $('#satuanBahanManualNama').val(unitNama);
                } else {
                    $('#satuanBahanManualId').val('').trigger('change');
                    $('#satuanBahanManualNama').val('-');
                }
            });

            // Cari form di dalam modal dan handle submitnya
            // Gunakan pendekatan delegasi atau find agar lebih akurat
            // Gunakan selektor ini agar pasti menangkap form di dalam modal
            $(document).on('submit', '#modalAddBahanManual form', function (e) {
                e.preventDefault();

                let form = $(this);
                let btnSubmit = form.find('button[type="submit"]');
                let originalText = btnSubmit.html();

                // Pastikan currentSubmissionId tidak null
                if (!currentSubmissionId) {
                    toastr.error('ID Pengajuan tidak ditemukan. Silakan refresh halaman.');
                    return;
                }

                // Ambil data dari input
                let selectedOption = $('#selectBahanManual').find(':selected');
                let bahanId = $('#selectBahanManual').val();
                let qty = $('#qtyBahanManual').val();
                let satuanId = $('#satuanBahanManualId').val(); // AMBIL DARI INPUT HIDDEN BARU

                if (!bahanId || !qty || !satuanId) {
                    toastr.warning('Bahan, Satuan, dan Qty wajib tersedia');
                    return;
                }

                // Beri efek loading agar user tidak klik berkali-kali
                btnSubmit.html('<i class="fas fa-spinner fa-spin"></i> Menambahkan...').prop('disabled', true);

                $.ajax({
                    url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/add-manual",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        bahan_baku_id: bahanId,
                        qty_digunakan: qty,
                        satuan_id: satuanId,
                        harga_total: 0 // Inisialisasi harga awal
                    },
                    success: function (res) {
                        $('#modalAddBahanManual').modal('hide');
                        form[0].reset();
                        // Reset tambahan untuk field manual
                        $('#satuanBahanManualId').val('').trigger('change');
                        $('#satuanBahanManualNama').val('-');
                        $('#selectBahanManual').val('').trigger('change');

                        loadAllData();
                        toastr.success('Item berhasil ditambahkan');
                    },
                    error: function (xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'Gagal menambah item';
                        toastr.error(errorMsg);
                    },
                    complete: function () {
                        // Kembalikan tombol ke keadaan semula
                        btnSubmit.html(originalText).prop('disabled', false);
                    }
                });
            });

            // --- HAPUS DETAIL ITEM ---
            $(document).on('click', '.btn-delete-detail', function () {
                let btn = $(this);
                let detailId = btn.data('id');

                confirmAction({
                    type: 'delete',
                    title: 'Konfirmasi Hapus Item',
                    message: `Yakin ingin menghapus item ini?`,
                    confirmText: 'Hapus',
                    onConfirm: function () {
                        $.ajax({
                            url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/detail/" + detailId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function () {
                                showNotificationPopUp('success', 'Item berhasil dihapus.', 'Berhasil');
                                loadAllData(); // REFRESH DATA
                            },
                            error: function () {
                                showNotificationPopUp('error', 'Gagal menghapus item.', 'Error');
                            }
                        });
                    }
                });
            });

            // --- HELPER LAINNYA ---
            $('#checkAll').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });

            function setReadonlyMode(isReadonly) {
                $('#wrapperDetails input, #wrapperDetails select').prop('disabled', isReadonly);
                $('#btnSplitOrder, #btnSimpanHarga, #btnTambahBahan, #checkAll').prop('disabled', isReadonly);
                if (isReadonly) {
                    $('.action-only, .btn-delete-detail, .btn-delete-child').addClass('d-none');
                } else {
                    $('.action-only, .btn-delete-detail, .btn-delete-child').removeClass('d-none');
                }
            }

            function toNumber(val) {
                if (val === null || val === undefined || val === '') return 0;
                val = val.toString().trim();

                // Hapus "Rp" dan spasi
                val = val.replace(/rp/gi, '').replace(/\s/g, '');

                // Deteksi format:
                // Jika ada koma, asumsikan format Indonesia (ribuan titik, desimal koma)
                // Contoh: 1.500,50 -> jadi 1500.50
                if (val.indexOf(',') !== -1) {
                    val = val.replace(/\./g, ''); // Hapus ribuan (titik)
                    val = val.replace(/,/g, '.'); // Ubah desimal (koma) jadi titik
                } else {
                    // Jika tidak ada koma, tapi ada titik, asumsikan itu desimal biasa (jika input type="number" step="any")
                    // Biarkan saja, atau handle ribuan jika input text
                }

                let num = parseFloat(val);
                return isNaN(num) ? 0 : num;
            }

        });

        // --- TOMBOL SELESAIKAN PENGAJUAN ---
        $('#btnSelesaiParent').on('click', function () {
            confirmAction({
                title: 'Selesaikan Pengajuan',
                message: 'Apakah Anda yakin ingin menyelesaikan pengajuan ini? Status akan dikunci dan tidak dapat diubah lagi.',
                confirmText: 'Ya, Selesaikan',
                onConfirm: function () {
                    // Gunakan form hidden yang sudah ada di HTML Anda
                    let form = $('#formUpdateStatus');
                    let url = "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/status";

                    form.attr('action', url);
                    $('#inputStatusFinal').val('selesai');
                    form.submit();
                }
            });
        });
    </script>
@endsection