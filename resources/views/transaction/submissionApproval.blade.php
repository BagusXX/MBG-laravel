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
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

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
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped" id="tableApproval">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Total</th>
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
                    <td>Rp {{ number_format($item->total_harga,0,',','.') }}</td>
                    <td>
                        <span class="badge badge-{{
                            $item->status === 'selesai' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning'))
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
                                <i class="fas fa-eye"></i> Detail
                            </button>

                            {{-- Tombol Cetak Invoice (Hanya Muncul Jika Status SELESAI) --}}
                            @if($item->status === 'selesai')
                                <a href="{{ route('transaction.submission-approval.print-parent-invoice', $item->id) }}" 
                                target="_blank" 
                                class="btn btn-secondary" 
                                title="Cetak Rekap Invoice">
                                    <i class="fas fa-print"></i>  Cetak Invoice
                                </a>
                            @endif
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
                <h5 class="modal-title">Detail Pengajuan: <strong id="modalTitleKode"></strong></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body modal-body-scroll">
                
                {{-- INFO & HEADER ACTIONS --}}
                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                    <table class="table-borderless" style="width: 50%">
                        <tr><td width="120" class="font-weight-bold">Dapur</td><td>: <span id="infoDapur"></span></td></tr>
                        <tr><td class="font-weight-bold">Tanggal</td><td>: <span id="infoTanggal"></span></td></tr>
                        <tr><td class="font-weight-bold">Status</td><td>: <span id="infoStatusBadge"></span></td></tr>
                    </table>

                    {{-- Actions --}}
                    <div id="wrapperActions">
                         {{-- Tombol Tolak (Muncul saat Diajukan) --}}
                        <button type="button" class="btn btn-danger d-none" id="btnTolakParent">
                            <i class="fas fa-times mr-2"></i> Tolak
                        </button>
                        {{-- Tombol Selesai (Muncul saat Diproses) --}}
                        <button type="button" class="btn btn-success btn-lg d-none" id="btnSelesaiParent">
                            <i class="fas fa-check-circle mr-2"></i> Selesaikan Pengajuan
                        </button>
                    </div>
                </div>

                {{-- PANEL SUPPLIER (SPLIT ORDER) --}}
                <div id="panelSupplier" class="d-none mb-4 p-3 bg-white rounded border shadow-sm">
                    <label class="font-weight-bold text-primary">Pilih Supplier untuk Barang Tercentang:</label>
                    <div class="row">
                        <div class="col-md-8">
                            <select id="selectSupplierSplit" class="form-control" style="width: 100%">
                                <option value="">- Memuat data... -</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary btn-block" id="btnSplitOrder">
                                <i class="fas fa-paper-plane mr-1"></i> Proses Split Order
                            </button>
                        </div>
                    </div>
                </div>

                {{-- TABEL RINCIAN --}}
                <form id="formUpdateHarga">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>Barang Operasional</th>
                                    <th width="90" class="text-center">Qty</th>
                                    <th width="80" class="text-center">Satuan</th>
                                    {{-- DUA KOLOM HARGA DITAMPILKAN --}}
                                    <th width="140" class="text-right">Hrg Dapur</th>
                                    <th width="140" class="text-right">Hrg Mitra</th>
                                    <th width="150" class="text-right">Subtotal</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="wrapperDetails">
                                {{-- Inject JS --}}
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="5" class="text-right font-weight-bold">Total Keseluruhan</td>
                                    <td class="text-right font-weight-bold" id="infoTotal"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-2">
                         <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTambahBahan">
                            <i class="fas fa-plus mr-1"></i> Tambah Item Manual
                        </button>
                        <button type="submit" class="btn btn-sm btn-warning" id="btnSimpanHarga">
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    let currentSubmissionId = null;
    let currentKitchenId = null;

    const formatRupiah = (num) => 'Rp ' + parseFloat(num).toLocaleString('id-ID', {minimumFractionDigits: 0});
    toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right" };

    $(document).ready(function() {
        
        $('#selectBahanManual').select2({ dropdownParent: $('#modalAddBahanManual') });
        $('#selectSupplierSplit').select2({ dropdownParent: $('#modalApproval') });

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
            let childId = $(this).data('id');
            
            if(confirm('Yakin ingin membatalkan/menghapus split order ini?')) {
                // Tampilkan loading sementara
                let btn = $(this);
                let originalContent = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: "{{ url('dashboard/transaksi/approval-menu/child') }}/" + childId,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        alert('Split order berhasil dihapus.');
                        loadAllData(); // Reload data modal agar list riwayat terupdate
                    },
                    error: function(xhr) {
                        btn.html(originalContent).prop('disabled', false);
                        let msg = 'Gagal menghapus data.';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            msg += '\n' + xhr.responseJSON.message;
                        }
                        alert(msg);
                    }
                });
            }
        });

        function loadAllData() {
            $.get("{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/data", function(data) {
                $('#modalTitleKode').text(data.kode);
                $('#infoTanggal').text(data.tanggal);
                $('#infoDapur').text(data.kitchen);
                
                let badgeClass = data.status === 'diproses' ? 'info' : (data.status === 'selesai' ? 'success' : 'warning');
                $('#infoStatusBadge').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);

                // Reset Tampilan Tombol
                $('#btnTolakParent, #btnSelesaiParent, #panelSupplier').addClass('d-none');
                
                // Logic Tampilan berdasarkan Status
                if (data.status === 'diajukan') {
                    $('#btnTolakParent').removeClass('d-none');
                    // Jika diajukan, kita anggap manager akan memulai proses -> tombol split belum muncul, 
                    // atau muncul tapi harus ubah status dulu? 
                    // Sesuai request sebelumnya: Diajukan -> tombol "Proses" -> berubah jadi Diproses.
                    // Di sini kita langsung tampilkan panel supplier jika user punya akses
                    $('#panelSupplier').removeClass('d-none'); // Asumsi manager bisa langsung split
                } else if (data.status === 'diproses') {
                    $('#btnSelesaiParent, #panelSupplier').removeClass('d-none');
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
                                            <button class="btn btn-sm btn-outline-danger btn-delete-child" 
                                                    data-id="${h.id}" 
                                                    title="Hapus Split Order">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Opsional: List Item Ringkas (Jika ingin seperti gambar referensi) --}}
                                    {{-- <ul class="mb-2 text-muted small pl-3">
                                        <li>Contoh Item...</li>
                                    </ul> --}}

                                    {{-- TOMBOL CETAK INVOICE (Posisi Kanan Bawah) --}}
                                    <div class="text-right mt-2 border-top pt-2">
                                        <a href="${invoiceUrl}" target="_blank" class="btn btn-sm btn-outline-secondary">
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

                data.forEach(item => {
                    // Logic: Jika harga mitra diisi (>0), pakai harga mitra. Jika tidak, pakai harga dapur.
                    // Ini hanya untuk tampilan subtotal sementara.
                    let hargaAktif = (parseFloat(item.harga_mitra) > 0) ? parseFloat(item.harga_mitra) : parseFloat(item.harga_dapur);
                    let subtotal = parseFloat(item.qty_digunakan) * hargaAktif;
                    grandTotal += subtotal;

                    let manualLabel = item.recipe_bahan_baku_id === null ? '<small class="text-info d-block font-italic">(Manual)</small>' : '';
                    let namaSatuan = item.bahan_baku?.unit?.satuan || '-';

                    html += `
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" class="check-item" value="${item.id}">
                            </td>
                            <td class="align-middle">
                                <span class="font-weight-bold text-dark">${item.bahan_baku?.nama || '-'}</span>
                                ${manualLabel}
                                <small class="text-muted">${item.bahan_baku?.unit?.nama || ''}</small>
                                <input type="hidden" name="details[${item.id}][id]" value="${item.id}">
                            </td>
                            <td class="align-middle px-1">
                                <input type="number" step="0.0001" class="form-control form-control-sm text-center bg-light" 
                                    name="details[${item.id}][qty_digunakan]" value="${parseFloat(item.qty_digunakan)}">
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-light border">${namaSatuan}</span>
                            </td>
                            
                            {{-- KOLOM HARGA DAPUR --}}
                            <td class="align-middle px-1">
                                <input type="number" class="form-control form-control-sm text-right" 
                                    name="details[${item.id}][harga_dapur]" 
                                    value="${parseFloat(item.harga_dapur || 0)}" placeholder="0">
                            </td>

                            {{-- KOLOM HARGA MITRA --}}
                            <td class="align-middle px-1">
                                <input type="number" class="form-control form-control-sm text-right border-info" 
                                    name="details[${item.id}][harga_mitra]" 
                                    value="${parseFloat(item.harga_mitra || 0)}" placeholder="0">
                            </td>

                            <td class="text-right align-middle font-weight-bold">
                                ${formatRupiah(subtotal)}
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-link text-danger btn-delete-detail" data-id="${item.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                if(data.length === 0) html = '<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada item bahan baku.</td></tr>';
                
                $('#wrapperDetails').html(html);
                $('#infoTotal').text(formatRupiah(grandTotal));
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

            // Debugging: Cek di Console browser apakah ID tertangkap
            console.log("Supplier:", supplierId);
            console.log("Items:", selectedIds);

            if(!supplierId) { alert('Harap pilih supplier!'); return; }
            if(selectedIds.length === 0) { alert('Harap centang minimal satu barang!'); return; }

            if(confirm(`Yakin ingin memproses ${selectedIds.length} item ke supplier ini?`)) {
                $.ajax({
                    url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/split",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        supplier_id: supplierId,
                        selected_details: selectedIds // Pastikan nama key ini sama dengan di $request->validate
                    },
                    success: function(res) {
                        alert('Order berhasil dipisah.');
                        $('#selectSupplierSplit').val('').trigger('change');
                        $('#checkAll').prop('checked', false);
                        // Penting: Reload data agar status berubah jadi 'DIPROSES' di tampilan
                        loadAllData(); 
                    },
                    error: function(xhr) {
                        // Tampilkan pesan error spesifik dari server jika ada
                        let msg = 'Gagal memproses.';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            msg += '\n' + xhr.responseJSON.message;
                        }
                        alert(msg);
                        console.error(xhr.responseText);
                    }
                });
            }
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
                    toastr.success(response.message || 'Data berhasil diperbarui'); // Gunakan toastr jika ada, atau alert
                    // alert('Data berhasil diperbarui'); 
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
                    
                    alert(msg);
                    console.error(xhr);
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
            if(confirm('Hapus item?')) {
                $.ajax({
                    url: "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/detail/" + $(this).data('id'),
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function() { loadDetails(); }
                });
            }
        });

        // Status Actions
        function updateStatus(status) {
            $('#formUpdateStatus').attr('action', "{{ url('dashboard/transaksi/approval-menu') }}/" + currentSubmissionId + "/status");
            $('#inputStatusFinal').val(status);
            $('#formUpdateStatus').submit();
        }

        $('#btnTolakParent').click(() => confirm('Tolak pengajuan ini?') && updateStatus('ditolak'));
        
        // Finalize (Selesai)
        $('#btnSelesaiParent').click(function () {
            if(confirm('Selesaikan seluruh pengajuan?')) {
                updateStatus('selesai');
            }
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