@extends('adminlte::page')

@section('title', 'Pengajuan Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Pengajuan Bahan Baku (Menu)</h1>
@endsection

@section('content')

    <div id="notification-container"></div>

    {{-- BUTTON ADD --}}
    <x-button-add idTarget="#modalAddSubmission" text="Tambah Pengajuan Menu" />

    {{-- FILTER SECTION --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Dapur</label>
                    <select id="filterKitchen" class="form-control">
                        <option value="">Semua Dapur</option>
                        @foreach($kitchens as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
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
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Tanggal</label>
                    <input type="date" id="filterDate" class="form-control">
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE DATA --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped" id="tableSubmission">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th width="15%">Tanggal Pengajuan</th>
                        <th width="15%">Tanggal Digunakan</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th class="text-center">Porsi Besar</th>
                        <th class="text-center">Porsi Kecil</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $item)
                                <tr data-kitchen="{{ $item->kitchen_id }}" data-status="{{ $item->status }}"
                                    data-date="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}">
                                    <td>{{ $item->kode }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('l, d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_digunakan)->locale('id')->translatedFormat('l, d-m-Y') }}
                                    </td>
                                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                                    <td>{{ $item->menu->nama ?? '-' }}</td>
                                    <td class="text-center">{{ $item->porsi_besar ?? 0 }}</td>
                                    <td class="text-center">{{ $item->porsi_kecil ?? 0 }}</td>
                                    <td>
                                        <span class="badge badge-{{
                        $item->status === 'diterima' ? 'success' :
                        ($item->status === 'selesai' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                                ($item->status === 'diajukan' ? 'warning' :
                                    ($item->status === 'ditolak' ? 'danger' : 'warning'))))
                                                }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- Tombol Detail --}}
                                        <button class="btn btn-info btn-sm btn-detail" data-id="{{ $item->id }}" data-toggle="modal"
                                            data-target="#modalDetail">
                                            Detail
                                        </button>

                                        {{-- Tombol Hapus (Hanya jika status diajukan) --}}
                                        @if($item->status === 'diajukan' || $item->status === 'ditolak')
                                            <x-button-delete idTarget="#modalDeleteSubmission" formId="formDeleteSubmission"
                                                action="{{ route('transaction.submission.destroy', $item->id) }}" text="Hapus" />
                                        @endif
                                    </td>
                                </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                Belum ada data pengajuan bahan baku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $submissions->links() }}
            </div>
        </div>
    </div>

    {{-- =========================
    MODAL TAMBAH
    ========================= --}}
    <x-modal-form id="modalAddSubmission" size="modal-xl" title="Tambah Pengajuan Bahan Baku"
        action="{{ route('transaction.submission.store') }}" submitText="Simpan Pengajuan">
        @csrf
        {{-- BARIS 1: Info Dasar --}}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Kode Pengajuan</label>
                    <input type="text" class="form-control" value="{{ $nextKode }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Digunakan</label>
                    <input type="date" name="tanggal_digunakan" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>

        {{-- BARIS 2: Dapur & Menu --}}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Dapur <span class="text-danger">*</span></label>
                    <select name="kitchen_id" id="selectKitchenStore" class="form-control" required>
                        <option value="">Pilih Dapur</option>
                        @foreach($kitchens as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Pilih Menu (Existing)</label>
                    <select name="menu_id" id="selectMenuStore" class="form-control" disabled>
                        <option value="">Pilih Dapur Terlebih Dahulu</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Atau Ketik Menu Baru</label>
                    <input type="text" name="nama_menu" id="inputNamaMenu" class="form-control"
                        placeholder="Isi jika menu belum ada di list">
                </div>
            </div>
        </div>

        {{-- BARIS 3: Porsi --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Porsi Besar</label>
                    <input type="number" name="porsi_besar" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Porsi Kecil</label>
                    <input type="number" name="porsi_kecil" class="form-control" min="0" value="0">
                </div>
            </div>
        </div>

        <hr>

        {{-- BARIS 4: INPUT ITEM MANUAL (DINAMIS) --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="font-weight-bold">Rincian Bahan Baku (Input Manual)</label>
            <button type="button" class="btn btn-sm btn-success" id="btnAddRow">
                <i class="fas fa-plus"></i> Tambah Item
            </button>
        </div>

        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-bordered table-sm" id="tableManualItems">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th width="35%">Bahan Baku</th>
                        <th width="15%">Qty</th>
                        <th width="15%">Satuan</th>
                        <th width="20%">Harga Dapur</th>
                        <th width="20%">Harga Mitra</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row ditambahkan via JS --}}
                </tbody>
            </table>
        </div>
    </x-modal-form>

    {{-- =========================
    MODAL DETAIL
    ========================= --}}
    <x-modal-detail id="modalDetail" size="modal-lg" title="Detail Pengajuan Bahan Baku">
        {{-- Header Info --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th width="40%">Kode</th>
                        <td>: <span id="det-kode">-</span></td>
                    </tr>
                    <tr>
                        <th>Tanggal Pengajuan</th>
                        <td>: <span id="det-tanggal">-</span></td>
                    </tr>
                    <tr>
                        <th>Tanggal Digunakan</th>
                        <td>: <span id="det-tanggal-digunakan">-</span></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: <span id="det-status">-</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th width="40%">Dapur</th>
                        <td>: <span id="det-dapur">-</span></td>
                    </tr>
                    <tr>
                        <th>Menu</th>
                        <td>: <span id="det-menu">-</span></td>
                    </tr>
                    <tr>
                        <th>Porsi Besar</th>
                        <td>: <span id="det-porsi-besar" class="font-weight-bold">-</span></td>
                    </tr>
                    <tr>
                        <th>Porsi Kecil</th>
                        <td>: <span id="det-porsi-kecil" class="font-weight-bold">-</span></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Table Items --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Bahan Baku</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Satuan</th>
                    </tr>
                </thead>
                <tbody id="det-tbody">
                    {{-- DATA AKAN DI-INJECT VIA JAVASCRIPT --}}
                </tbody>
            </table>
        </div>

        <div id="loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status"></div>
            <p>Memuat data...</p>
        </div>

        {{-- RIWAYAT SPLIT ORDER --}}
        <div id="sectionRiwayat" class="mt-4 pt-3 border-top">
            <h6 class="font-weight-bold text-secondary mb-3">Riwayat Approval (Split Order)</h6>
            <div id="wrapperRiwayat">
                {{-- Inject JS --}}
            </div>
        </div>
    </x-modal-detail>

    {{-- =========================
    MODAL DELETE
    ========================= --}}
    <x-modal-delete id="modalDeleteSubmission" formId="formDeleteSubmission" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus pengajuan ini?" />

@endsection

@section('js')
    <script>
        // ==========================================
        // 1. HELPER FUNCTIONS
        // ==========================================

        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        };

        const formatDate = (dateString) => {
            if (!dateString) return '-';
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        };

        const formatQty = (number) => {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(number);
        };

        // ==========================================
        // 2. DATA MASTER
        // ==========================================
        const masterBahan = @json($bahanBakus);
        const masterUnit = @json($units);

        $(document).ready(function () {

            // ==========================================
            // 3. FILTER TABLE LOGIC
            // ==========================================
            $('#filterKitchen, #filterStatus, #filterDate').on('change', function () {
                let kitchen = $('#filterKitchen').val();
                let status = $('#filterStatus').val()?.toLowerCase() || '';
                let date = $('#filterDate').val();

                $('#tableSubmission tbody tr').each(function () {
                    let rKitchen = $(this).data('kitchen');
                    let rStatus = $(this).data('status')?.toLowerCase() || '';
                    let rDate = $(this).data('date') || '';

                    let show = true;
                    if (kitchen && String(rKitchen) !== String(kitchen)) show = false;
                    if (status && rStatus !== status) show = false;
                    if (date && rDate !== date) show = false;
                    $(this).toggle(show);
                });
            });

            // ==========================================
            // 4. MENU SELECTION LOGIC
            // ==========================================

            $('#selectKitchenStore').on('change', function () {
                let kitchenId = $(this).val();
                let menuSelect = $('#selectMenuStore');
                let inputMenu = $('#inputNamaMenu');

                menuSelect.empty().prop('disabled', true).append('<option value="">Pilih Dapur Terlebih Dahulu</option>');
                inputMenu.val('').prop('disabled', false);

                if (!kitchenId) return;

                menuSelect.empty().append('<option value="">Sedang memuat menu...</option>');

                let url = "{{ route('transaction.submission.menu-by-kitchen', ['kitchenId' => 'FAKE_ID']) }}".replace('FAKE_ID', kitchenId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        menuSelect.empty();
                        inputMenu.prop('disabled', false);

                        if (Array.isArray(data) && data.length > 0) {
                            menuSelect.prop('disabled', false);
                            menuSelect.append('<option value="">-- Pilih Menu Existing --</option>');
                            $.each(data, function (_, menu) {
                                menuSelect.append(`<option value="${menu.id}">${menu.nama}</option>`);
                            });
                        } else {
                            menuSelect.prop('disabled', true);
                            menuSelect.append('<option value="">Menu tidak tersedia (Silakan ketik baru)</option>');
                        }
                    },
                    error: function (xhr) {
                        console.error("Error fetching menu:", xhr);
                        menuSelect.empty().prop('disabled', true).append('<option value="">Gagal memuat menu</option>');
                    }
                });
            });

            $('#selectMenuStore').on('change', function () {
                if ($(this).val()) {
                    $('#inputNamaMenu').val('').prop('disabled', true);
                } else {
                    $('#inputNamaMenu').prop('disabled', false);
                }
            });

            $('#inputNamaMenu').on('input', function () {
                if ($(this).val().length > 0) {
                    $('#selectMenuStore').val('').prop('disabled', true);
                } else {
                    $('#selectMenuStore').prop('disabled', false);
                }
            });

            // ==========================================
            // 5. DYNAMIC ROW (MANUAL ITEM INPUT)
            // ==========================================
            let rowIdx = 0;

            function addRow() {
                let optionsBahan = '<option value="">-- Pilih Bahan --</option>';
                masterBahan.forEach(b => {
                    optionsBahan += `<option value="${b.id}">${b.nama}</option>`;
                });

                let optionsUnit = '<option value="">-- Satuan --</option>';
                masterUnit.forEach(u => {
                    optionsUnit += `<option value="${u.id}">${u.satuan}</option>`;
                });

                let tr = `
                        <tr id="row-${rowIdx}">
                            <td>
                                <select name="items[${rowIdx}][bahan_baku_id]" class="form-control form-control-sm" required>
                                    ${optionsBahan}
                                </select>
                            </td>
                            <td>
                                <input type="number" step="any" name="items[${rowIdx}][qty]" class="form-control form-control-sm text-center" placeholder="0" required>
                            </td>
                            <td>
                                <select name="items[${rowIdx}][satuan_id]" class="form-control form-control-sm" required>
                                    ${optionsUnit}
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" name="items[${rowIdx}][harga_dapur]" class="form-control" placeholder="Total">
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" name="items[${rowIdx}][harga_mitra]" class="form-control" placeholder="Total (Opsional)">
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-xs btn-remove-row" data-id="${rowIdx}" title="Hapus Baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                $('#tableManualItems tbody').append(tr);
                rowIdx++;
            }

            $('#btnAddRow').on('click', function () {
                addRow();
            });

            $('#tableManualItems').on('click', '.btn-remove-row', function () {
                let id = $(this).data('id');
                $('#row-' + id).remove();
            });

            addRow();

            // ==========================================
            // 6. DETAIL MODAL (AJAX FETCH)
            // ==========================================
            $('.btn-detail').on('click', function () {
                let id = $(this).data('id');
                let url = "{{ route('transaction.submission.data', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', id);

                $('#det-tbody').empty();
                $('#wrapperRiwayat').empty();
                $('#loading-spinner').show();
                $('.table-responsive, #sectionRiwayat').hide();

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $('#det-kode').text(data.kode);
                        $('#det-tanggal').text(data.tanggal);
                        $('#det-tanggal-digunakan').text(data.tanggal_digunakan);
                        $('#det-dapur').text(data.kitchen);
                        $('#det-menu').text(data.menu);
                        $('#det-porsi-besar').text(data.porsi_besar || 0);
                        $('#det-porsi-kecil').text(data.porsi_kecil || 0);

                        let badgeClass = 'secondary';
                        if (data.status === 'diajukan') badgeClass = 'warning';
                        else if (data.status === 'diproses') badgeClass = 'info';
                        else if (data.status === 'selesai' || data.status === 'diterima') badgeClass = 'success';
                        else if (data.status === 'ditolak') badgeClass = 'danger';
                        $('#det-status').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);

                        // Details dari controller.data menggunakan endpoint /details
                        let detailUrl = "{{ route('transaction.submission-approval.details', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', id);

                        $.ajax({
                            url: detailUrl,
                            type: 'GET',
                            dataType: 'json',
                            success: function (details) {
                                let rows = '';
                                if (details && details.length > 0) {
                                    $.each(details, function (index, item) {
                                        rows += `
                                                <tr>
                                                    <td>${item.nama_bahan || '-'}</td>
                                                    <td class="text-center">${formatQty(item.qty)}</td>
                                                    <td class="text-center">${item.nama_satuan || '-'}</td>
                                                </tr>
                                            `;
                                    });
                                } else {
                                    rows = '<tr><td colspan="3" class="text-center text-muted">Tidak ada rincian bahan baku</td></tr>';
                                }
                                $('#det-tbody').html(rows);
                            }
                        });

                        let historyHtml = '';
                        if (data.history && data.history.length > 0) {
                            $.each(data.history, function (i, h) {
                                let invoiceUrl = "{{ route('transaction.submission-approval.invoice', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', h.id);

                                historyHtml += `
                                        <div class="card mb-2 border" style="background-color: #f8f9fa;">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <strong class="text-primary">${h.kode}</strong> 
                                                        <span class="text-muted mx-2">|</span> 
                                                        <i class="fas fa-truck mr-1 text-secondary"></i> ${h.supplier_nama}
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-${h.status === 'diproses' ? 'info' : 'success'} mr-2">${h.status.toUpperCase()}</span>
                                                        <strong class="text-dark">${formatRupiah(h.total)}</strong>
                                                    </div>
                                                </div>
                                                <div class="text-muted small mb-2">
                                                    ${h.item_count} item bahan baku
                                                </div>
                                                <div class="text-right border-top pt-2">
                                                    <a href="${invoiceUrl}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                                        <i class="fas fa-print mr-1"></i> Cetak Invoice
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                            });
                        } else {
                            historyHtml = '<div class="text-muted font-italic text-center py-2 border rounded bg-light">Belum ada riwayat split order.</div>';
                        }
                        $('#wrapperRiwayat').html(historyHtml);

                        $('#loading-spinner').hide();
                        $('.table-responsive, #sectionRiwayat').slideDown();
                    },
                    error: function (xhr) {
                        console.error("Detail Error:", xhr);
                        $('#loading-spinner').hide();
                        alert('Gagal mengambil data detail: ' + (xhr.responseJSON?.message || 'Server Error'));
                    }
                });
            });
            // ==========================================
            // 6. DETAIL MODAL (AJAX FETCH)
            // ==========================================
            $('.btn-detail').on('click', function () {
                let id = $(this).data('id');
                let url = "{{ route('transaction.submission.data', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', id);

                $('#det-tbody').empty();
                $('#wrapperRiwayat').empty();
                $('#loading-spinner').show();
                $('.table-responsive, #sectionRiwayat').hide();

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log('Full Response:', data);
                        console.log('Details:', data.details);

                        // A. Populate Header Info
                        $('#det-kode').text(data.kode);
                        $('#det-tanggal').text(data.tanggal);
                        $('#det-tanggal-digunakan').text(data.tanggal_digunakan);
                        $('#det-dapur').text(data.kitchen);
                        $('#det-menu').text(data.menu);
                        $('#det-porsi-besar').text(data.porsi_besar || 0);
                        $('#det-porsi-kecil').text(data.porsi_kecil || 0);

                        let badgeClass = 'secondary';
                        if (data.status === 'diajukan') badgeClass = 'warning';
                        else if (data.status === 'diproses') badgeClass = 'info';
                        else if (data.status === 'selesai' || data.status === 'diterima') badgeClass = 'success';
                        else if (data.status === 'ditolak') badgeClass = 'danger';
                        $('#det-status').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);

                        // B. Populate Table Detail Items LANGSUNG dari data.details
                        let rows = '';
                        if (data.details && data.details.length > 0) {
                            console.log('Processing details:', data.details.length);

                            $.each(data.details, function (index, item) {
                                console.log(`Detail ${index}:`, item);

                                rows += `
                            <tr>
                                <td>${item.nama_bahan || '-'}</td>
                                <td class="text-center">${formatQty(item.qty)}</td>
                                <td class="text-center">${item.nama_satuan || '-'}</td>
                            </tr>
                        `;
                            });
                        } else {
                            console.warn('No details array or empty');
                            rows = '<tr><td colspan="3" class="text-center text-muted">Tidak ada rincian bahan baku</td></tr>';
                        }
                        $('#det-tbody').html(rows);

                        // C. Populate Riwayat Split Order (History)
                        let historyHtml = '';
                        if (data.history && data.history.length > 0) {
                            $.each(data.history, function (i, h) {
                                let invoiceUrl = "{{ route('transaction.submission-approval.invoice', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', h.id);

                                historyHtml += `
                            <div class="card mb-2 border" style="background-color: #f8f9fa;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong class="text-primary">${h.kode}</strong> 
                                            <span class="text-muted mx-2">|</span> 
                                            <i class="fas fa-truck mr-1 text-secondary"></i> ${h.supplier_nama}
                                        </div>
                                        <div>
                                            <span class="badge badge-${h.status === 'diproses' ? 'info' : 'success'} mr-2">${h.status.toUpperCase()}</span>
                                            <strong class="text-dark">${formatRupiah(h.total)}</strong>
                                        </div>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        ${h.item_count} item bahan baku
                                    </div>
                                    <div class="text-right border-top pt-2">
                                        <a href="${invoiceUrl}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                            <i class="fas fa-print mr-1"></i> Cetak Invoice
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                            });
                        } else {
                            historyHtml = '<div class="text-muted font-italic text-center py-2 border rounded bg-light">Belum ada riwayat split order.</div>';
                        }
                        $('#wrapperRiwayat').html(historyHtml);

                        // Stop Loading
                        $('#loading-spinner').hide();
                        $('.table-responsive, #sectionRiwayat').slideDown();
                    },
                    error: function (xhr) {
                        console.error("AJAX Error:", xhr);
                        console.error("Status:", xhr.status);
                        console.error("Response Text:", xhr.responseText);
                        $('#loading-spinner').hide();

                        let errorMsg = 'Server Error';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert('Gagal mengambil data detail: ' + errorMsg);
                    }
                });
            });

        });
    </script>
@endsection