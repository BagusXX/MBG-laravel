@extends('adminlte::page')

@section('title', 'Pengajuan Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('css')
    <style>
        .modal-huge {
            max-width: 95% !important;
        }
    </style>
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
                        <th class="text-center">PM Besar</th>
                        <th class="text-center">PM Kecil</th>
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
                                        
                                        {{-- Tombol Edit (hanya jika diajukan) --}}
                                        @if($item->status === 'diajukan')
                                            <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $item->id }}" data-toggle="modal" data-target="#modalEditSubmission">
                                                Edit
                                            </button>
                                        @endif

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
                    <label>PM Besar</label>
                    <input type="number" name="porsi_besar" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>PM Kecil</label>
                    <input type="number" name="porsi_kecil" class="form-control" min="0" value="0">
                </div>
            </div>
        </div>

        <hr>

        {{-- BARIS 4: INPUT ITEM MANUAL (DINAMIS) --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="font-weight-bold">Rincian Bahan Baku (Input Manual)</label>
            
        </div>

        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-bordered table-sm" id="tableManualItems">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th width="20%">Bahan Baku</th>
                        <th width="10%">Qty</th>
                        <th width="10%">Satuan</th>
                        <th width="15%">Harga Dapur</th>
                        <th width="15%">Harga Mitra</th>
                        <th width="15%">Subtotal Dapur</th>
                        <th width="15%">Subtotal Mitra</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row ditambahkan via JS --}}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right text-end"> 
                            <button type="button" class="btn btn-sm btn-success" id="btnAddRow">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-modal-form>

    {{-- =========================
    MODAL EDIT
    ========================= --}}
    <x-modal-form id="modalEditSubmission" size="modal-xl" title="Edit Pengajuan Bahan Baku"
        action="#" submitText="Perbarui Pengajuan" formId="formEditSubmission">
        @csrf
        <input type="hidden" name="_method" value="PATCH">

        {{-- BARIS 1: Info Dasar --}}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Kode Pengajuan</label>
                    <input type="text" id="edit-kode" class="form-control" value="-" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" id="edit-tanggal" class="form-control" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal Digunakan</label>
                    <input type="date" name="tanggal_digunakan" id="edit-tanggal-digunakan" class="form-control">
                </div>
            </div>
        </div>

        {{-- BARIS 2: Dapur & Menu --}}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Dapur</label>
                    <select name="kitchen_id" id="selectKitchenEdit" class="form-control" disabled>
                        @foreach($kitchens as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Pilih Menu (Existing)</label>
                    <select name="menu_id" id="selectMenuEdit" class="form-control">
                        <option value="">-- Pilih Menu --</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Atau Ketik Menu Baru</label>
                    <input type="text" name="nama_menu" id="inputNamaMenuEdit" class="form-control" placeholder="Isi jika menu belum ada di list">
                </div>
            </div>
        </div>

        {{-- BARIS 3: Porsi --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>PM Besar</label>
                    <input type="number" name="porsi_besar" id="edit-porsi-besar" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>PM Kecil</label>
                    <input type="number" name="porsi_kecil" id="edit-porsi-kecil" class="form-control" min="0" value="0">
                </div>
            </div>
        </div>

        <hr>

        {{-- BARIS 4: INPUT ITEM MANUAL (DINAMIS) --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="font-weight-bold">Rincian Bahan Baku (Input Manual)</label>
        </div>

        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-bordered table-sm" id="tableManualItemsEdit">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th width="20%">Bahan Baku</th>
                        <th width="10%">Qty</th>
                        <th width="10%">Satuan</th>
                        <th width="15%">Harga Dapur</th>
                        <th width="15%">Harga Mitra</th>
                        <th width="15%">Subtotal Dapur</th>
                        <th width="15%">Subtotal Mitra</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row akan diinject via JS --}}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right text-end"> 
                            <button type="button" class="btn btn-sm btn-success" id="btnAddRowEdit">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>
                        </td>
                    </tr>
                </tfoot>
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
                        <th>PM Besar</th>
                        <td>: <span id="det-porsi-besar" class="font-weight-bold">-</span></td>
                    </tr>
                    <tr>
                        <th>PM Kecil</th>
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

        const formatRupiah = (num) => {
            // Validasi: jika null, undefined, atau bukan angka, jadikan 0
            let value = parseFloat(num);
            if (isNaN(value)) value = 0;
            
            return 'Rp ' + value.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
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
        let currentBahanList = [];

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

                let bahanUrl = "{{ route('transaction.submission.bahan-by-kitchen', ['kitchenId' => 'FAKE_ID']) }}"
                    .replace('FAKE_ID', kitchenId);

                $.ajax({
                    url: bahanUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        currentBahanList = data;

                        // update semua dropdown bahan baku yang sudah ada
                        refreshAllBahanDropdown();
                    },
                    error: function (xhr) {
                        console.error("Error fetching bahan:", xhr);
                        currentBahanList = [];
                        refreshAllBahanDropdown();
                    }
                });
                $('#tableManualItems tbody').empty();
                rowIdx = 0;
                addRow();

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

            // Fungsi Hitung Otomatis
        $(document).on('input', '.input-qty, .input-harga-dapur, .input-harga-mitra', function() {
            // Cari baris (tr) terdekat dari input yang sedang diketik
            let row = $(this).closest('tr');
            
            // Ambil nilai-nilainya
            let qty = parseFloat(row.find('.input-qty').val()) || 0;
            let hargaDapur = parseFloat(row.find('.input-harga-dapur').val()) || 0;
            let hargaMitra = parseFloat(row.find('.input-harga-mitra').val()) || 0;
            
            // Hitung
            let subtotalDapur = qty * hargaDapur;
            let subtotalMitra = qty * hargaMitra;
            
            // Tampilkan di kolom readonly (Formatted dengan formatRupiah jika ingin, atau angka biasa)
            row.find('.total-dapur').val(subtotalDapur.toLocaleString('id-ID'));
            row.find('.total-mitra').val(subtotalMitra.toLocaleString('id-ID'));
        });

            // ==========================================
            // 5. DYNAMIC ROW (MANUAL ITEM INPUT)
            // ==========================================
            let rowIdx = 0;

            function addRow() {
                let optionsBahan = '<option value="">-- Pilih Bahan --</option>';
                (currentBahanList.length ? currentBahanList : []).forEach(b => {
                    optionsBahan += `<option value="${b.id}">${b.nama}</option>`;
                });

                let optionsUnit = '<option value="">Satuan</option>';
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
                                <input type="number" step="any" name="items[${rowIdx}][qty]" class="form-control form-control-sm text-center input-qty" placeholder="0" required>
                            </td>
                            <td>
                                <select name="items[${rowIdx}][satuan_id]" class="form-control form-control-sm" required>
                                    ${optionsUnit}
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="items[${rowIdx}][harga_dapur]" class="form-control input-harga-dapur" placeholder="Harga">
                                </div>
                            </td>
                            
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="items[${rowIdx}][harga_mitra]" class="form-control input-harga-mitra" placeholder="Harga">
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm total-dapur" readonly placeholder="0">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm total-mitra" readonly placeholder="0">
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

            function refreshAllBahanDropdown() {
                let options = `<option value="">-- Pilih Bahan --</option>`;

                if (currentBahanList.length > 0) {
                    currentBahanList.forEach(b => {
                        options += `<option value="${b.id}">${b.nama}</option>`;
                    });
                } else {
                    options += `<option value="">(Tidak ada bahan baku)</option>`;
                }

                $('#tableManualItems tbody select[name*="[bahan_baku_id]"]').each(function () {
                    let selected = $(this).val(); // simpan pilihan lama
                    $(this).html(options);
                    $(this).val(selected); // restore kalau masih ada
                });
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

                                // 1. BUILD HTML UNTUK ITEM BAHAN BAKU (Seragamkan dengan Approval)
                                let itemsHtml = '';
                                if (h.items && h.items.length > 0) {
                                    h.items.forEach(item => {
                                        itemsHtml += `
                                            <li>
                                                ${item.nama}
                                                <span class="text-muted small">
                                                    (${formatQty(item.qty)} ${item.unit} x ${formatRupiah(item.harga_tampil)}) 
                                                </span>
                                            </li>
                                        `;
                                    });
                                } else {
                                    itemsHtml = `<li class="text-muted font-italic small">Tidak ada item</li>`;
                                }

                                // 2. MASUKKAN KE DALAM CARD (Gunakan Border & Style yang sama)
                                historyHtml += `
                                    <div class="card mb-2 border shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong class="text-dark">${h.kode}</strong> 
                                                    <span class="text-muted mx-2">|</span> 
                                                    <i class="fas fa-truck mr-1 text-secondary"></i> ${h.supplier_nama || h.supplier || '-'}
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-${h.status === 'disetujui' || h.status === 'selesai' ? 'success' : 'info'} mr-3 px-2 py-1">
                                                        ${h.status.toUpperCase()}
                                                    </span>
                                                    <strong class="text-dark">${formatRupiah(h.total)}</strong>
                                                </div>
                                            </div>
                                            
                                            <ul class="mb-0 pl-3" style="font-size: 0.9em; list-style-type: disc; color: #555;">
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
                            historyHtml = '<div class="text-muted font-italic text-center py-3 border bg-light rounded">Belum ada riwayat split order.</div>';
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

            // ==========================================
            // 7. EDIT MODAL LOGIC
            // ==========================================
            let currentBahanListEdit = [];
            let rowEditIdx = 0;

            function addRowEdit(data = {}) {
                let optionsBahan = '<option value="">-- Pilih Bahan --</option>';
                (currentBahanListEdit.length ? currentBahanListEdit : []).forEach(b => {
                    optionsBahan += `<option value="${b.id}">${b.nama}</option>`;
                });

                let optionsUnit = '<option value="">Satuan</option>';
                masterUnit.forEach(u => {
                    optionsUnit += `<option value="${u.id}">${u.satuan}</option>`;
                });

                const idx = rowEditIdx;

                let initialSubDapur = data.subtotal_dapur ? parseFloat(data.subtotal_dapur).toLocaleString('id-ID') : '0';
                let initialSubMitra = data.subtotal_mitra ? parseFloat(data.subtotal_mitra).toLocaleString('id-ID') : '0';

                let tr = `
                    <tr id="row-edit-${idx}">
                        <td>
                            <select name="items[${idx}][bahan_baku_id]" class="form-control form-control-sm">
                                ${optionsBahan}
                            </select>
                        </td>
                        <td>
                            <input type="number" step="any" name="items[${idx}][qty]" class="form-control form-control-sm text-center input-qty" placeholder="0">
                        </td>
                        <td>
                            <select name="items[${idx}][satuan_id]" class="form-control form-control-sm">
                                ${optionsUnit}
                            </select>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" name="items[${idx}][harga_dapur]" class="form-control input-harga-dapur" placeholder="Harga Dapur">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" name="items[${idx}][harga_mitra]" class="form-control input-harga-mitra" placeholder="Harga Mitra">
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm total-dapur bg-light" readonly value="${initialSubDapur}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm total-mitra bg-light" readonly value="${initialSubMitra}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs btn-remove-row" data-id="${idx}" title="Hapus Baris">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#tableManualItemsEdit tbody').append(tr);

                // set values if provided
                if (data.bahan_baku_id) {
                    $(`#row-edit-${idx} select[name$='[bahan_baku_id]']`).val(data.bahan_baku_id);
                }
                if (data.qty !== undefined) {
                    $(`#row-edit-${idx} input[name$='[qty]']`).val(data.qty);
                }
                if (data.satuan_id) {
                    $(`#row-edit-${idx} select[name$='[satuan_id]']`).val(data.satuan_id);
                }
                if (data.harga_dapur !== undefined) {
                    $(`#row-edit-${idx} input[name$='[harga_dapur]']`).val(data.harga_dapur);
                }
                if (data.harga_mitra !== undefined) {
                    $(`#row-edit-${idx} input[name$='[harga_mitra]']`).val(data.harga_mitra);
                }

                rowEditIdx++;
            }

            function refreshAllBahanDropdownEdit() {
                let options = `<option value="">-- Pilih Bahan --</option>`;
                if (currentBahanListEdit.length > 0) {
                    currentBahanListEdit.forEach(b => {
                        options += `<option value="${b.id}">${b.nama}</option>`;
                    });
                } else {
                    options += `<option value="">(Tidak ada bahan baku)</option>`;
                }

                $('#tableManualItemsEdit tbody select[name*="[bahan_baku_id]"]').each(function () {
                    let selected = $(this).val();
                    $(this).html(options);
                    $(this).val(selected);
                });
            }

            $('#btnAddRowEdit').on('click', function () {
                addRowEdit();
            });

            $('#tableManualItemsEdit').on('click', '.btn-remove-row', function () {
                let id = $(this).data('id');
                $('#row-edit-' + id).remove();
            });

            // Open edit modal and populate
            $('.btn-edit').on('click', function () {
                let id = $(this).data('id');
                let urlTemplate = "{{ route('transaction.submission.update', ['submission' => 'FAKE_ID']) }}";
                let formAction = urlTemplate.replace('FAKE_ID', id);

                console.log("Target Update URL:", formAction)
                
                let $form = $('#formEditSubmission');

                // // --- PENGECEKAN PENTING ---
                // if ($form.length === 0) {
                //     alert("CRITICAL ERROR: Form dengan ID #formEditSubmission tidak ditemukan! Cek file component x-modal-form Anda.");
                //     return; 
                // }
                
                $form.attr('action', formAction);
                
                // clear
                $('#tableManualItemsEdit tbody').empty();
                rowEditIdx = 0;
                
                let dataUrl = "{{ route('transaction.submission.data', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', id);
                $.ajax({
                    url: dataUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log('Data Edit:', data); // DEBUG 1: Cek apakah kitchen_id ada di sini
                        
                        // 1. SET TANGGAL (Gunakan key _raw yang formatnya YYYY-MM-DD)
                        if (data.tanggal_raw) {
                            $('#edit-tanggal').val(data.tanggal_raw);
                        }

                        if (data.tanggal_digunakan_raw) {
                            $('#edit-tanggal-digunakan').val(data.tanggal_digunakan_raw);
                        }

                        $('#edit-kode').val(data.kode || '');

                        // FIX DAPUR: Pastikan ID-nya sesuai (Cek apakah di HTML id-nya benar 'selectKitchenEdit')
                        if (data.kitchen_id) {
                        // Cek apakah elemen select ditemukan
                        if ($('#selectKitchenEdit').length === 0) {
                            console.error('ID #selectKitchenEdit tidak ditemukan di HTML!');
                        }
                        
                        // Set value dan trigger change (opsional, tapi aman)
                        $('#selectKitchenEdit').val(data.kitchen_id).trigger('change'); 

                        // 3. LOAD MENU BERDASARKAN DAPUR
                        let menuUrl = "{{ route('transaction.submission.menu-by-kitchen', ['kitchenId' => 'FAKE_ID']) }}".replace('FAKE_ID', data.kitchen_id);
                        
                        $.ajax({
                            url: menuUrl,
                            type: 'GET',
                            dataType: 'json',
                            success: function (menus) {
                                let $sel = $('#selectMenuEdit');
                                $sel.empty();
                                if (Array.isArray(menus) && menus.length > 0) {
                                    $sel.append('<option value="">-- Pilih Menu Existing --</option>');
                                    menus.forEach(m => $sel.append(`<option value="${m.id}">${m.nama}</option>`));
                                } else {
                                    $sel.append('<option value="">(Tidak ada menu)</option>');
                                }

                                // Set Menu Terpilih
                                if (data.menu_id) {
                                    $sel.val(data.menu_id);
                                    $('#inputNamaMenuEdit').val('').prop('disabled', true);
                                } else {
                                    $sel.val('');
                                    $('#inputNamaMenuEdit').val(data.menu || '').prop('disabled', false);
                                }
                            }
                        });

                        // 4. LOAD BAHAN BAKU BERDASARKAN DAPUR (KUNCI PERBAIKAN)
                        let bahanUrl = "{{ route('transaction.submission.bahan-by-kitchen', ['kitchenId' => 'FAKE_ID']) }}".replace('FAKE_ID', data.kitchen_id);
                        
                        $.ajax({
                            url: bahanUrl,
                            type: 'GET',
                            dataType: 'json',
                            success: function (bahan) {
                                // Simpan ke variable global khusus Edit
                                currentBahanListEdit = bahan || []; 

                                // Setelah master bahan termuat, baru render baris itemnya
                                if (data.details && data.details.length > 0) {
                                    data.details.forEach(d => {
                                        addRowEdit({
                                            bahan_baku_id: d.bahan_baku_id,
                                            qty: d.qty || d.qty_digunakan, // Sesuaikan dengan nama kolom di JSON
                                            satuan_id: d.satuan_id,
                                            harga_dapur: d.harga_dapur,
                                            harga_mitra: d.harga_mitra,
                                            subtotal_dapur: d.subtotal_dapur,
                                            subtotal_mitra: d.subtotal_mitra
                                        });
                                    });
                                } else {
                                    addRowEdit(); // Tambah baris kosong jika tidak ada detail
                                }
                                
                                // Hitung ulang/trigger input agar total muncul
                                $('.input-qty').trigger('input');
                                
                                // Update dropdown bahan baku di semua baris yang baru dibuat
                                refreshAllBahanDropdownEdit();
                            },
                            error: function(xhr) {
                                console.error('Gagal ambil bahan:', xhr);
                                currentBahanListEdit = [];
                                addRowEdit();
                            }
                        });

                    } else {
                        alert('Data Kitchen ID tidak ditemukan dari server. Cek Controller.');
                    }

                        // porsi
                        $('#edit-porsi-besar').val(data.porsi_besar || 0);
                        $('#edit-porsi-kecil').val(data.porsi_kecil || 0);
                    },
                    error: function (xhr) {
                        alert('Gagal memuat data pengajuan: ' + (xhr.responseJSON?.message || 'Server Error'));
                    }
                });
            });

            // Menu select / input toggles for edit modal
            $('#selectMenuEdit').on('change', function () {
                if ($(this).val()) {
                    $('#inputNamaMenuEdit').val('').prop('disabled', true);
                } else {
                    $('#inputNamaMenuEdit').prop('disabled', false);
                }
            });

            $('#inputNamaMenuEdit').on('input', function () {
                if ($(this).val().length > 0) {
                    $('#selectMenuEdit').val('').prop('disabled', true);
                } else {
                    $('#selectMenuEdit').prop('disabled', false);
                }
            });

        });
    </script>
@endsection