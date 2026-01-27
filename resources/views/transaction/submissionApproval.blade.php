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
        .table-middle td { vertical-align: middle; }

        input[type=number] {
            -moz-appearance: textfield; /* Untuk Firefox */
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
                    <th>Tanggal Pengajuan</th>
                    <th>Dapur</th>
                    <th>Menu</th>
                    <th>Porsi</th>
                    {{-- <th>Total</th> --}}
                    <th>Status</th>
                    <th width="100" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $item)
                <tr
                    data-kitchen="{{ strtolower($item->kitchen->nama ?? '') }}"
                    data-status="{{ strtolower($item->status) }}"
                    data-date="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                >
                    <td>{{ $item->kode }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                    <td>{{ $item->menu ? $item->menu->nama : '-' }}</td>
                    <td>{{ $item->porsi ? $item->porsi : '-' }}</td>
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
                            <button class="btn btn-primary btn-proses" 
                                    data-id="{{ $item->id }}" 
                                    data-kitchen-id="{{ $item->kitchen_id }}"
                                    title="Detail / Review">
                                Detail
                            </button>

                            {{-- Tombol Cetak Invoice (Hanya Muncul Jika Status SELESAI) --}}
                            {{-- @if($item->status === 'selesai')
                                <a href="{{ route('transaction.submission-approval.print-parent-invoice', $item->id) }}" 
                                target="_blank" 
                                class="btn btn-secondary" 
                                title="Cetak Rekap Invoice">
                                    <i class="fas fa-print"></i>  Cetak Invoice
                                </a>
                            @endif --}}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $submissions->links() }}
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
                        <table class="table-borderless" style="width: 50%">
                            <tr><th width="120" class="py-1">Kode</th><td class="py-1">: <span id="modalTitleKode"></span></td></tr>
                            <tr><th width="60%"class="py-1">Tanggal Pengajuan</th><td class="py-1">: <span id="infoTanggal"></span></td></tr>
                            <tr><th class="py-1 ">Status</th><td class="py-1">: <span id="infoStatusBadge"></span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table>
                            <tr><th class="py-1">Menu</th><td class="py-1">: <span id="infoMenu"></span></td></tr>
                            <tr><th width="120" class="py-1">Dapur</th><td class="py-1">: <span id="infoDapur"></span></td></tr>
                            <tr><th class="py-1">Porsi</th><td class="py-1">: <span id="infoPorsi"></span></td></tr>
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
    
                                <select 
                                    id="selectSupplierSplit"
                                    class="form-control"
                                    {{-- style="width: 100%" --}}
                                    required
                                >
                                    <option value="" selected disabled>- Pilih Supplier Khusus Dapur Ini -</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <button type="button"
                                    class="btn btn-primary btn-block action-only"
                                    id="btnSplitOrder">
                                <i class="fas fa-paper-plane mr-1"></i>
                                Proses Split Order
                            </button>
                        </div>
                    </div>

                {{-- </div> --}}

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
    let isReadonlyStatus = false; // <--- TAMBAHKAN INI

    const formatRupiah = (num) => 'Rp ' + parseFloat(num).toLocaleString('id-ID', {minimumFractionDigits: 0});
    toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right" };

    // Helper Format Qty (2 desimal, koma)
    const formatQty = (number) => {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
    };

    $(document).ready(function() {
        
        $('#selectBahanManual').select2({ dropdownParent: $('#modalAddBahanManual') });
        // $('#selectSupplierSplit').select2({ dropdownParent: $('#modalApproval') });

        $('form').on('wheel', 'input[type=number]', function(e) {
            $(this).blur();
        });

        // --- BUKA MODAL ---
        $('.btn-proses').on('click', function() {
            currentSubmissionId = $(this).data('id');
            currentKitchenId = $(this).data('kitchen-id');
            loadAllData();
            $('#modalApproval').modal('show');
        });

        // --- HAPUS CHILD SUBMISSION (SPLIT ORDER) ---
        $(document).on('click', '.btn-delete-child', function() {
            let btn = $(this);
            let childId = btn.data('id');
            
            confirmAction({
                type: 'delete',
                title: 'Konfirmasi Hapus',
                message: `Yakin ingin menghapus split order ini?`,
                confirmText: 'Hapus',
                onConfirm: function() {
                    // Tampilkan loading sementara
                    let originalContent = btn.html();
                    btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
    
                    $.ajax({
                        url: "{{ url('dashboard/transaksi/approval-menu/child') }}/" + childId,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            btn.html(originalContent).prop('disabled', false);
    
                            showNotificationPopUp('success', 'Split order berhasil dihapus.', 'Berhasil');
                            loadAllData(); // Reload data modal agar list riwayat terupdate
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message ?? 'Gagal menghapus data.';
                            showNotificationPopUp('error', msg, 'Terjadi Kesalahan');
                        },
    
                        complete: function () {
                            btn.html(originalContent).prop('disabled', false)
                        }
                    });
                }
            });
        });

        $('#confirmActionModal').on('hidden.bs.modal', function () {
            $('body').addClass('modal-open');
        });

        function loadAllData() {
            $.get("{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/data", function(data) {
                $('#modalTitleKode').text(data.kode);
                $('#infoTanggal').text(data.tanggal);
                $('#infoMenu').text(data.menu)
                $('#infoPorsi').text(data.porsi)
                $('#infoDapur').text(data.kitchen);
                
                let badgeClass = data.status === 'diproses' ? 'info' : (data.status === 'selesai' ? 'success' : 'warning');
                $('#infoStatusBadge').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);

                isReadonlyStatus = (data.status === 'selesai');

                // Reset Tampilan Tombol
                $('#btnTolakParent, #btnSelesaiParent, #panelSupplier').addClass('d-none');

                // PASTIKAN tombol aksi muncul kembali (default)
                $('.action-only').removeClass('d-none');

                setReadonlyMode(false);

                // Logic Tampilan berdasarkan Status
                if (data.status === 'diajukan') {
                    $('#btnTolakParent').removeClass('d-none');
                    $('#panelSupplier').removeClass('d-none');
                } else if (data.status === 'diproses') {
                    $('#btnSelesaiParent, #panelSupplier').removeClass('d-none');
                } else if (data.status === 'selesai') {
                    // MODE READONLY
                    $('.action-only').addClass('d-none'); // Sembunyikan tombol Simpan, Tambah, Split
                    setReadonlyMode(true); // Matikan input form
                }

                let supplierOpts = '<option value="">- Pilih Supplier Khusus Dapur Ini -</option>';
                if (data.suppliers && data.suppliers.length > 0) {
                    data.suppliers.forEach(s => {
                        supplierOpts += `<option value="${s.id}">${s.nama}</option>`;
                    });
                } else {
                    supplierOpts = '<option value="" disabled>Tidak ada supplier untuk dapur ini</option>';
                }
                
                // Masukkan HTML option ke dalam Select
                $('#selectSupplierSplit').html(supplierOpts);

                // Render Riwayat
                let historyHtml = '';
                if(data.history && data.history.length > 0) {
                    data.history.forEach(h => {
                        let invoiceUrl = "{{ url('dashboard/transaksi/approval-menu') }}/" + h.id + "/invoice";
                        let itemsHtml = '';
                        if (h.items && h.items.length > 0) {
                            h.items.forEach(item => {
                                itemsHtml += `
                                    <li>
                                        ${item.nama}
                                        <span class="text-muted">(${formatQty(item.qty)} x ${formatRupiah(item.harga)})</span>
                                    </li>
                                `;
                            });
                        } else {
                            itemsHtml = `<li class="text-muted font-italic">Tidak ada item</li>`;
                        }
                        historyHtml += `
                            <div class="card mb-2 border">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong class="text-dark" style="font-size: 1.1em;">${h.kode}</strong> 
                                            <span class="text-muted mx-2">|</span> 
                                            <i class="fas fa-truck mr-1 text-secondary"></i> ${h.supplier_nama}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-success mr-3 px-2 py-1">DISETUJUI</span>
                                            <strong class="mr-3 text-dark" style="font-size: 1.1em;">${formatRupiah(h.total)}</strong>
                                            
                                            {{-- Tombol Hapus --}}
                                            <button class="btn btn-sm btn-outline-danger btn-delete-child action-only" 
                                                    data-id="${h.id}" 
                                                    title="Hapus Split Order">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Opsional: List Item Ringkas (Jika ingin seperti gambar referensi) --}}
                                    <ul class="mb-0 pl-3" style="font-size: 0.9em;">
                                        ${itemsHtml}
                                    </ul>

                                    {{-- TOMBOL CETAK INVOICE (Posisi Kanan Bawah) --}}
                                    <div class="text-right mt-2 border-top pt-2">
                                        <a href="${invoiceUrl}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-print mr-1"></i> Cetak Invoice
                                        </a>
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    historyHtml = '<div class="text-muted font-italic text-center py-2">Belum ada riwayat split order.</div>';
                }
                $('#wrapperRiwayat').html(historyHtml);
            });

            loadDetails();
        }

        function loadDetails() {
            $.get("{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/details", function(data) {
                let html = '';
                let grandTotal = 0;
                let isReadonly = false;

                data.forEach(item => {
                    // Logic: Jika harga mitra diisi (>0), pakai harga mitra. Jika tidak, pakai harga dapur.
                    // Ini hanya untuk tampilan subtotal sementara.
                    let hargaAktif = (parseFloat(item.harga_mitra) > 0) ? parseFloat(item.harga_mitra) : parseFloat(item.harga_dapur);
                    // let subtotal = parseFloat(item.qty_digunakan) * hargaAktif;
                    grandTotal += subtotal;

                    let manualLabel = item.recipe_bahan_baku_id === null ? '<small class="text-info d-block font-italic">(Manual)</small>' : '';
                    let namaSatuan = item.bahan_baku?.unit?.satuan || '-';

                    html += `
                        <tr>
                            <td class="text-center action-only">
        <input type="checkbox" class="check-item" value="${item.id}">
    </td>

    <td>
        <strong>${item.nama}</strong>
    </td>

    <td class="text-center">
        ${item.display_qty}
    </td>

    <td class="text-center">
        <span class="badge badge-light">${item.display_unit}</span>
    </td>

    <td class="text-right">
        ${formatRupiah(item.harga_dapur)}
    </td>

    <td class="text-right">
        ${formatRupiah(item.harga_mitra ?? item.harga_dapur)}
    </td>

    <td class="text-right font-weight-bold">
        ${formatRupiah(item.subtotal)}
    </td>
                        </tr>
                    `;
                });

                if(data.length === 0) html = '<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada item bahan baku.</td></tr>';
                
                $('#wrapperDetails').html(html);
                $('#infoTotal').text(formatRupiah(grandTotal));

                if (isReadonlyStatus) {
                    setReadonlyMode(true);
                }
                // --
            });
        }

        // Check All
        $('#checkAll').on('change', function() {
            $('.check-item').prop('checked', $(this).prop('checked'));
        });

        // Split Order
        $('#btnSplitOrder').on('click', function() {
            let supplierId = $('#selectSupplierSplit').val();
            let selectedIds = [];
            
            // Pastikan class '.check-item' sesuai dengan yang ada di render tabel HTML
            $('.check-item:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if(!supplierId) {
                showNotificationPopUp('warning', 'Harap pilih supplier!')
                return; 
            }

            if(selectedIds.length === 0) {
                showNotificationPopUp('warning', 'Harap centang minimal satu barang!')
                return; 
            }

            confirmAction({
                title: 'Konfirmasi Split Order',
                message: `Yakin ingin memproses ${selectedIds.length} item ke supplier ini?`,
                confirmText: 'Proses',
                onConfirm: function() {
                    $.ajax({
                        url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/split",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            supplier_id: supplierId,
                            selected_details: selectedIds // Pastikan nama key ini sama dengan di $request->validate
                        },
                        success: function(res) {
                            showNotificationPopUp('success', 'Order berhasil dipisah.', 'Berhasil')
                            
                            $('#selectSupplierSplit').val('').trigger('change');
                            $('#checkAll').prop('checked', false);
                            // Penting: Reload data agar status berubah jadi 'DIPROSES' di tampilan
                            loadAllData(); 
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message ?? 'Gagal memproses.';
                            showNotificationPopUp('error', msg, 'Terjadi Kesalahan');
                        }
                    });
                }
            });
        });

        // --- UPDATE HARGA (FORM SUBMIT) ---
        $('#formUpdateHarga').on('submit', function(e) {
            e.preventDefault();
            
            // Disable tombol simpan agar tidak double klik
            let btn = $('#btnSimpanHarga');
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/update-harga",
                type: 'PATCH',
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                success: function(response) {
                    showNotificationPopUp('success', response.message || 'Data berhasil diperbarui', 'Berhasil')

                    loadDetails();
                    loadAllData(); // Reload header total
                },
                error: function(xhr) {
                    // LOGIKA ERROR HANDLING LEBIH BAIK
                    let msg = 'Gagal menyimpan perubahan.';
                    
                    if (xhr.responseJSON) {
                        // Jika error validasi Laravel (422)
                        if (xhr.responseJSON.errors) {
                            msg += '\n';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                msg += '- ' + value[0] + '\n';
                            });
                        } 
                        // Jika error message biasa (403/500)
                        else if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                    }
                    
                    showNotificationPopUp('error', msg, 'Terjadi Kesalahan');
                },
                complete: function() {
                    // Kembalikan tombol seperti semula
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Add Manual
        $('#btnTambahBahan').click(function() {
             let url = "{{ route('transaction.submission-approval.helper.bahan-baku', ['kitchen' => 'FAKE_ID']) }}".replace('FAKE_ID', currentKitchenId);
             $('#selectBahanManual').empty().append('<option>Loading...</option>');
             $.get(url, function(data) {
                 let opts = '<option value="">Pilih Bahan</option>';
                 data.forEach(b => opts += `<option value="${b.id}">${b.nama} (${b.unit?.satuan})</option>`);
                 $('#selectBahanManual').html(opts);
                 $('#modalAddBahanManual').modal('show');
             });
        });

        $('#modalAddBahanManual form').submit(function(e) {
            e.preventDefault();
            $.post("{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/add-manual", {
                _token: '{{ csrf_token() }}',
                bahan_baku_id: $('#selectBahanManual').val(),
                qty_digunakan: $('#qtyBahanManual').val()
            }, function() {
                $('#modalAddBahanManual').modal('hide');
                loadDetails();
            });
        });

        // Delete Detail
        $(document).on('click', '.btn-delete-detail', function() {
            let btn = $(this);
            let childId = btn.data('id');
            // if(confirm('Hapus item?')) {
            confirmAction({
                type: 'delete',
                title: 'Konfirmasi Hapus',
                message: `Yakin ingin menghapus item ini?`,
                confirmText: 'Hapus',
                onConfirm: function() {
                    $.ajax({
                        url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/detail/" + childId,
                        type: 'DELETE',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function() {
                            showNotificationPopUp('success', 'Item berhasil dihapus.', 'Berhasil');
                            loadDetails();
                        },

                        error: function() {
                            showNotificationPopUp('error', 'Gagal menghapus item.');
                        }
                    });
                }
            });
        });

        function setReadonlyMode(isReadonly) {
            // Disable input & checkbox
            $('#wrapperDetails input, #wrapperDetails select').prop('disabled', isReadonly);

            // Disable tombol aksi
            $('#btnSplitOrder').prop('disabled', isReadonly);
            $('#btnSimpanHarga').prop('disabled', isReadonly);
            $('#btnTambahBahan').prop('disabled', isReadonly);
            $('#checkAll').prop('disabled', isReadonly);

            // Hide tombol delete detail
            if (isReadonly) {
                $('.action-only').addClass('d-none'); // Sembunyikan kolom
                $('.btn-delete-detail').addClass('d-none');
                $('.btn-delete-child').addClass('d-none');
            } else {
                $('.action-only').removeClass('d-none'); // Munculkan kolom
                $('.btn-delete-detail').removeClass('d-none');
                $('.btn-delete-child').removeClass('d-none');
            }
        }

        // Status Actions
        function updateStatus(status) {
            $('#formUpdateStatus').attr('action', "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/status");
            $('#inputStatusFinal').val(status);
            $('#formUpdateStatus').submit();
        }

        $('#btnTolakParent').click(() => confirm('Tolak pengajuan ini?') && updateStatus('ditolak'));
        
        // Finalize (Selesai)
        $('#btnSelesaiParent').click(function () {
            confirmAction({
                type: 'success',
                title: 'Konfirmasi Selesai',
                message: `Selesaikan seluruh pengajuan?`,
                confirmText: 'Selesai',
                onConfirm: function() {
                    updateStatus('selesai');
                }
            });
        });

        // Filter Frontend
        $('#filterKitchen, #filterStatus, #filterDate').on('change', function() {
            let kitchen = $('#filterKitchen').val()?.toLowerCase() || '';
            let status = $('#filterStatus').val()?.toLowerCase() || '';
            let date = $('#filterDate').val();

            $('#tableApproval tbody tr').each(function () {
                let rKitchen = $(this).data('kitchen')?.toLowerCase() || '';
                let rStatus = $(this).data('status')?.toLowerCase() || '';
                let rDate = $(this).data('date') || '';
                let show = (kitchen === '' || rKitchen === kitchen) &&
                           (status === '' || rStatus === status) &&
                           (date === '' || rDate === date);
                $(this).toggle(show);
            });
        });

        // FIX SCROLL: Mengatasi masalah scroll hilang saat modal kedua ditutup
        $('#modalAddBahanManual').on('hidden.bs.modal', function () {
            // Cek apakah modal approval (modal utama) masih terbuka
            if ($('#modalApproval').hasClass('show')) {
                // Paksa tambahkan class modal-open ke body agar scroll tetap jalan
                $('body').addClass('modal-open');
            }
        });

    });
</script>
@endsection