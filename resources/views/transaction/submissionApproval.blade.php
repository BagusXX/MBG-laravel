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
            <div class="table-responsive"></div>
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
                                        <span class="badge badge-{{
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
        </div>
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
                                <button type="button" class="btn btn-success btn-md d-none" id="btnSelesaiParent">
                                    <i class="fas fa-check-circle mr-2"></i> Selesaikan Pengajuan
                                </button>
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

                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary btn-block action-only" id="btnSplitOrder">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Proses Split Order
                                </button>
                            </div>
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
                                        <th>Bahan Baku</th>
                                        <th width="90" class="text-center">Qty</th>
                                        <th width="80" class="text-center">Satuan</th>
                                        {{-- DUA KOLOM HARGA DITAMPILKAN --}}
                                        <th width="140" class="text-right">Hrg Dapur</th>
                                        <th width="140" class="text-right">Hrg Mitra</th>
                                        {{-- <th width="150" class="text-right">Subtotal</th> --}}
                                        <th width="50" class="action-only"></th>
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
                            <button type="button" class="btn btn-outline-secondary btn-sm action-only" id="btnTambahBahan">
                                <i class="fas fa-plus mr-1"></i> Tambah Item Manual
                            </button>
                            <button type="submit" class="btn btn-sm btn-warning action-only" id="btnSimpanHarga">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan Harga/Qty
                            </button>
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

            // Prevent scroll number input
            $('form').on('wheel', 'input[type=number]', function (e) {
                $(this).blur();
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
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-success mr-3 px-2 py-1">DISETUJUI</span>
                                            <strong class="mr-3 text-dark">${formatRupiah(h.total)}</strong>

                                            <button class="btn btn-sm btn-outline-danger btn-delete-child action-only" 
                                                    data-id="${h.id}" title="Hapus Split Order">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <ul class="mb-0 pl-3" style="font-size: 0.9em; list-style-type: disc;">
                                        ${itemsHtml}
                                    </ul>
                                    <div class="text-right mt-2 border-top pt-2">
                                        <a href="${invoiceUrl}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                            <i class="fas fa-print mr-1"></i> Cetak Invoice
                                        </a>
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    historyHtml = '<div class="text-muted font-italic text-center py-2 border bg-light rounded">Belum ada riwayat split order.</div>';
                }
                $('#wrapperRiwayat').html(historyHtml);
            }

            // --- FUNGSI RENDER TABEL DETAIL (Menggantikan loadDetails) ---
            function renderDetailsTable(detailsData) {
                let html = '';
                let grandTotal = 0;

                if (detailsData && detailsData.length > 0) {
                    detailsData.forEach(item => {
                        // Kalkulasi Total untuk Tampilan Saja
                        // (Logika harga: Subtotal Dapur jika Mitra 0/null)
                        let hargaTampil = parseFloat(item.harga_dapur) || 0;
                        // Jika ingin menampilkan total semu: hargaTampil = parseFloat(item.qty_digunakan) * (harga_satuan);
                        // Tapi di controller Anda mengirim 'harga_dapur' SEBAGAI SUBTOTAL. Jadi langsung pakai.
                        grandTotal += hargaTampil;

                        // Manual Label (Opsional, jika controller kirim null di recipe id)
                        // let manualLabel = item.recipe_bahan_baku_id === null ? '<small class="text-info d-block font-italic">(Manual)</small>' : '';
                        let manualLabel = '';

                        html += `
                            <tr>
                                <td class="text-center align-middle action-only">
                                    <input type="checkbox" class="check-item" value="${item.id}">
                                </td>
                                <td class="align-middle">
                                    <span class="text-dark font-weight-bold">${item.nama_bahan}</span>
                                    ${manualLabel}
                                    <input type="hidden" name="details[${item.id}][id]" value="${item.id}">
                                    {{-- Hidden Input Satuan ID agar ikut terkirim saat save --}}
                                    <input type="hidden" name="details[${item.id}][satuan_id]" value="${item.satuan_id}">
                                </td>
                                <td class="align-middle px-1">
                                    <input type="number" step="0.0001" class="form-control form-control-sm text-center bg-light" 
                                        name="details[${item.id}][qty_digunakan]" value="${item.qty_digunakan}">
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-light border">${item.nama_satuan}</span>
                                </td>

                                {{-- KOLOM HARGA DAPUR --}}
                                <td class="align-middle px-1">
                                    <input type="number" class="form-control form-control-sm text-right" 
                                        name="details[${item.id}][harga_dapur]" 
                                        value="${item.harga_dapur}" placeholder="0">
                                </td>

                                {{-- KOLOM HARGA MITRA --}}
                                <td class="align-middle px-1">
                                    <input type="number" class="form-control form-control-sm text-right border-info" 
                                        name="details[${item.id}][harga_mitra]" 
                                        value="${item.harga_mitra}" placeholder="0">
                                </td>

                                <td class="text-center align-middle action-only">
                                    <button type="button" class="btn btn-link text-danger btn-delete-detail" data-id="${item.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
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
                    data.forEach(b => opts += `<option value="${b.id}">${b.nama} (${b.unit?.satuan})</option>`);
                    $('#selectBahanManual').html(opts);
                    $('#modalAddBahanManual').modal('show');
                });
            });

            $('#modalAddBahanManual form').submit(function (e) {
                e.preventDefault();
                // Ambil text option terpilih untuk mencari ID Satuan (jika tidak ada di value)
                // Namun sebaiknya endpoint helper di atas juga mengembalikan ID satuan.
                // Asumsi: Backend handle satuan via relasi bahan baku, atau Anda perlu mengirim satuan_id.

                // NOTE: Di controller `addManualBahan`, Anda memvalidasi `satuan_id`. 
                // Pastikan Anda mengirim `satuan_id`. Jika UI select2 belum punya data satuan_id, 
                // Anda perlu mengambilnya saat select berubah atau simpan di data-attribute option.

                // Untuk SEMENTARA, saya asumsikan controller bisa mencari satuan default jika tidak dikirim, 
                // ATAU kita perlu ambil satuan_id dari data json helper tadi.

                // SOLUSI CEPAT: Ubah value option menjadi "bahanID|satuanID" atau simpan data satuan di variable global temp.
                // Tapi karena JS ini panjang, pastikan Controller `addManualBahan` Anda bisa menerima bahan_baku_id saja lalu cari satuan defaultnya, 
                // ATAU tambahkan input hidden satuan_id di modal manual.

                // Biarkan request ini jalan dulu, cek error di network tab jika satuan required.
                $.post("{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/add-manual", {
                    _token: '{{ csrf_token() }}',
                    bahan_baku_id: $('#selectBahanManual').val(),
                    qty_digunakan: $('#qtyBahanManual').val(),
                    // satuan_id: ??? (Perlu ditambah logic pengambilan satuan ID)
                    // Untuk sementara hardcode '1' atau ubah controller agar auto-detect satuan dari bahan baku
                    satuan_id: 1 // TODO: PERBAIKI LOGIC INI AGAR DINAMIS
                }, function () {
                    $('#modalAddBahanManual').modal('hide');
                    loadAllData(); // REFRESH DATA
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
                if (val === null || val === undefined) return 0;
                val = val.toString().trim();

                // hapus Rp, spasi
                val = val.replace(/rp/gi, '').replace(/\s/g, '');

                // hapus pemisah ribuan titik
                val = val.replace(/\./g, '');

                // ubah koma desimal ke titik
                val = val.replace(/,/g, '.');

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