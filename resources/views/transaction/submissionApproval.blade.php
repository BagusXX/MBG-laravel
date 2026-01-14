@extends('adminlte::page')

@section('title', 'Persutujuan Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
    <style>
        .table-middle td { vertical-align: middle !important; }
        .modal-body-scroll { max-height: 70vh; overflow-y: auto; }
    </style>
@endsection

@section('content_header')
    <h1>Persetujuan Menu</h1>
@endsection

@section('content')

<div id="notification-container"></div>

{{-- FILTER SECTION --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Dapur</label>
                <select id="filterKitchen" class="form-control">
                    <option value="">Semua Dapur</option>
                    @foreach($kitchens as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
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
        <table class="table table-bordered table-striped table-middle" id="tableApproval">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Menu (Porsi)</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th width="120" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr data-kitchen="{{ $item->kitchen->nama ?? '' }}" 
                    data-status="{{ $item->status }}" 
                    data-date="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}">
                    <td>{{ $item->kode }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                    <td>
                        <strong>{{ $item->menu->nama ?? '-' }}</strong><br>
                        <small class="text-muted">{{ $item->porsi }} Porsi</small>
                    </td>
                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
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
                        <button class="btn btn-primary btn-sm btn-proses" 
                                data-id="{{ $item->id }}" 
                                title="Proses Approval">
                            <i class="fas fa-edit"></i> Review
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data pengajuan.</td>
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
    MODAL APPROVAL UTAMA (AJAX BASED)
========================= --}}
<div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">Review Pengajuan: <span id="modalTitleKode"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body modal-body-scroll">
                {{-- Info Header --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Dapur & Menu</small>
                        <h6 class="font-weight-bold" id="infoDapurMenu"></h6>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted d-block">Porsi</small>
                        <h6 class="font-weight-bold" id="infoPorsi"></h6>
                    </div>
                    <div class="col-md-4 text-right">
                        <small class="text-muted d-block">Total Biaya Dapur</small>
                        <h5 class="text-primary font-weight-bold" id="infoTotal"></h5>
                    </div>
                </div>

                <hr>

                {{-- Section 1: Update Harga & Qty --}}
                <h6 class="font-weight-bold mb-3"><i class="fas fa-list mr-2"></i>Rincian Bahan Baku & Penyesuaian Harga</h6>
                <form id="formUpdateHarga">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th width="120">Qty (Unit)</th>
                                <th width="150">Harga Dapur (IDR)</th>
                                <th width="150">Harga Mitra (IDR)</th>
                                <th width="180">Subtotal Dapur</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="wrapperDetails">
                            {{-- Inject by AJAX --}}
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-success btn-sm" id="btnTambahBahan">
                            <i class="fas fa-plus mr-1"></i> Tambah Bahan Manual
                        </button>
                        <button type="submit" class="btn btn-success" id="btnSaveHarga">
                            <i class="fas fa-save mr-1"></i> Update Perubahan Data
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                {{-- Section 2: Split ke Supplier --}}
                <div id="sectionSplitSupplier" class="d-none">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-truck mr-2"></i>Teruskan ke Supplier (Split Order)</h6>
                    <form action="" id="formSplitSupplier" method="POST" class="row align-items-end">
                        @csrf
                        <div class="col-md-6">
                            <label>Pilih Supplier</label>
                            <select name="supplier_id" class="form-control" required>
                                <option value="" disabled selected>Pilih Supplier untuk Bahan ini</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i> Buat Child Submission (PO Supplier)
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Section 3: Finalisasi Parent --}}
                <div id="sectionFinalStatus" class="mt-4 pt-3 border-top text-center d-none">
                    <button type="button" class="btn btn-danger mr-2" id="btnTolakParent">Tolak Pengajuan</button>
                    <button type="button" class="btn btn-success px-5" id="btnSelesaiParent">Selesaikan Pengajuan (Close PO)</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH BAHAN MANUAL --}}
<x-modal-form id="modalAddBahanManual" title="Tambah Bahan Baku Manual" action="" submitText="Tambahkan">
    <div class="form-group">
        <label>Pilih Bahan Baku</label>
        <select id="selectBahanManual" class="form-control select2" style="width: 100%"></select>
    </div>
    <div class="form-group">
        <label>Jumlah (Qty)</label>
        <input type="number" id="qtyBahanManual" class="form-control" step="0.0001" min="0.0001">
    </div>
</x-modal-form>

@endsection

@section('js')
<script>
    let currentSubmissionId = null;

    $(document).ready(function() {
        // --- 1. LOAD DATA KE MODAL ---
        $('.btn-proses').on('click', function() {
            currentSubmissionId = $(this).data('id');
            loadSubmissionData();
            loadDetails();
            $('#modalApproval').modal('show');
        });

        function loadSubmissionData() {
            $.get(`/transaction/submission-approval/${currentSubmissionId}/data`, function(data) {
                $('#modalTitleKode').text(data.kode);
                $('#infoDapurMenu').text(`${data.kitchen} - ${data.menu}`);
                $('#infoPorsi').text(`${data.porsi} Porsi`);
                
                // Toggle sections berdasarkan status
                $('#sectionSplitSupplier, #sectionFinalStatus').toggleClass('d-none', data.status !== 'diproses');
                if(data.status === 'diajukan') {
                    // Update status ke diproses secara otomatis jika baru dibuka? 
                    // Tergantung workflow Anda, di controller ada splitToSupplier yang butuh status diproses
                }
            });
        }

        function loadDetails() {
            $.get(`/transaction/submission-approval/${currentSubmissionId}/details`, function(data) {
                let html = '';
                let grandTotal = 0;
                data.forEach(item => {
                    let sub = item.qty_digunakan * item.harga_dapur;
                    grandTotal += sub;
                    html += `
                        <tr class="table-middle">
                            <td>
                                <strong>${item.bahan_baku.nama}</strong><br>
                                <small class="text-muted">${item.bahan_baku.unit.nama}</small>
                                <input type="hidden" name="details[${item.id}][id]" value="${item.id}">
                            </td>
                            <td><input type="number" step="0.0001" name="details[${item.id}][qty_digunakan]" class="form-control form-control-sm" value="${item.qty_digunakan}"></td>
                            <td><input type="number" name="details[${item.id}][harga_dapur]" class="form-control form-control-sm" value="${item.harga_dapur}"></td>
                            <td><input type="number" name="details[${item.id}][harga_mitra]" class="form-control form-control-sm" value="${item.harga_mitra || item.harga_dapur}"></td>
                            <td class="text-right font-weight-bold">Rp ${sub.toLocaleString('id-ID')}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-link text-danger btn-delete-detail" data-id="${item.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#wrapperDetails').html(html);
                $('#infoTotal').text(`Rp ${grandTotal.toLocaleString('id-ID')}`);
            });
        }

        // --- 2. UPDATE HARGA ---
        $('#formUpdateHarga').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.post(`/transaction/submission-approval/${currentSubmissionId}/update-harga`, formData + '&_token={{ csrf_token() }}', function() {
                toastr.success('Harga & Qty berhasil diperbarui');
                loadDetails();
            });
        });

        // --- 3. TAMBAH BAHAN MANUAL ---
        $('#btnTambahBahan').on('click', function() {
            // Load bahan baku via AJAX berdasarkan kitchen pengajuan ini
            $.get(`/transaction/submission-approval/kitchen-bahan/${currentSubmissionId}`, function(data) {
                let options = '<option value="">Pilih Bahan</option>';
                data.forEach(b => options += `<option value="${b.id}">${b.nama} (${b.unit.nama})</option>`);
                $('#selectBahanManual').html(options);
                $('#modalAddBahanManual').modal('show');
            });
        });

        $('#modalAddBahanManual form').on('submit', function(e) {
            e.preventDefault();
            $.post(`/transaction/submission-approval/${currentSubmissionId}/add-manual`, {
                _token: '{{ csrf_token() }}',
                bahan_baku_id: $('#selectBahanManual').val(),
                qty_digunakan: $('#qtyBahanManual').val()
            }, function() {
                $('#modalAddBahanManual').modal('hide');
                loadDetails();
            });
        });

        // --- 4. DELETE DETAIL ---
        $(document).on('click', '.btn-delete-detail', function() {
            let id = $(this).data('id');
            if(confirm('Hapus bahan baku ini dari pengajuan?')) {
                $.post(`/transaction/submission-approval/${currentSubmissionId}/detail/${id}`, {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                }, function() {
                    loadDetails();
                });
            }
        });

        // --- 5. SPLIT SUPPLIER ---
        $('#formSplitSupplier').on('submit', function(e) {
            $(this).attr('action', `/transaction/submission-approval/${currentSubmissionId}/split`);
        });

        // --- 6. FILTER LOGIC ---
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
    });
</script>
@endsection