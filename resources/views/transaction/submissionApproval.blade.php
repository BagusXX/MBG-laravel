@extends('adminlte::page')

@section('title', 'Daftar Permintaan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Daftar Permintaan (Approval)</h1>
@endsection

@section('content')

    <x-notification-pop-up />

    {{-- FILTER SECTION --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Dapur</label>
                    <select id="filterKitchen" class="form-control">
                        <option value="">Semua Dapur</option>
                        @foreach ($kitchens as $kitchen)
                            <option value="{{ $kitchen->nama }}">{{ $kitchen->nama }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Filter lain bisa ditambahkan di sini --}}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th>Porsi</th>
                        <th>Status</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $item)
                        <tr data-kitchen="{{ $item->kitchen->nama }}">
                            <td>{{ $item->kode }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $item->kitchen->nama }}</td>
                            <td>{{ $item->menu->nama }}</td>
                            <td>{{ $item->porsi }}</td>
                            <td>
                                <span class="badge badge-{{ $item->status === 'selesai' ? 'success' : ($item->status === 'ditolak' ? 'danger' : ($item->status === 'diproses' ? 'info' : 'warning')) }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                            <td>
                                {{-- VIEW DETAIL (JIKA SELESAI/DITOLAK) --}}
                                @if($item->status === 'selesai' || $item->status === 'ditolak')
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalViewDetail{{ $item->id }}">
                                        Detail
                                    </button>
                                @else
                                    {{-- EDIT DETAIL & APPROVAL (JIKA AKTIF) --}}
                                    <button type="button" class="btn btn-primary btn-sm btnEditDetail"
                                        data-toggle="modal" data-target="#modalEditDetail"
                                        data-id="{{ $item->id }}"
                                        data-action="{{ route('transaction.approval.update', $item->id) }}">
                                        Detail & Proses
                                    </button>

                                    {{-- TOMBOL PROSES (STATUS UPDATE) --}}
                                    @if($item->status === 'diajukan')
                                        <form action="{{ route('transaction.approval.process', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Proses permintaan ini?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-warning btn-sm">Proses</button>
                                        </form>
                                    @elseif($item->status === 'diproses')
                                        <button type="button" class="btn btn-success btn-sm btnCompleteSubmission"
                                            data-toggle="modal" data-target="#modalCompleteSubmission"
                                            data-id="{{ $item->id }}"
                                            data-kode="{{ $item->kode }}"
                                            data-action="{{ route('transaction.approval.complete', $item->id) }}">
                                            Selesai
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $submissions->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>

    {{-- MODAL EDIT DETAIL (CORE LOGIC APPROVAL) --}}
    <x-modal-form id="modalEditDetail" size="modal-xl" title="Edit Detail Permintaan" action="" submitText="Simpan Perubahan">
        @method('PUT')
        
        {{-- Info Header (Read Only) --}}
        <table class="table table-borderless table-sm">
            <tr><th width="120">Kode</th><td id="modal_detail_kode">: -</td></tr>
            <tr><th>Dapur</th><td id="modal_detail_dapur">: -</td></tr>
            <tr><th>Menu</th><td id="modal_detail_menu">: -</td></tr>
            <tr><th>Porsi</th><td id="modal_detail_porsi">: -</td></tr>
        </table>

        <hr>
        <h6 class="font-weight-bold">Detail Bahan Baku & Harga</h6>
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Bahan Baku</th>
                        <th width="100">Qty</th>
                        <th>Satuan</th>
                        <th width="140">Harga Dapur</th>
                        <th width="140">Harga Mitra</th>
                        <th>Subtotal Dapur</th>
                        <th>Subtotal Mitra</th>
                        <th width="50">#</th>
                    </tr>
                </thead>
                <tbody id="edit_bahan_tbody">
                    <tr><td colspan="8" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <hr>
        <h6 class="font-weight-bold">Tambah Bahan Manual (Opsional)</h6>
        <div class="form-row align-items-end" id="tambah-bahan-wrapper">
            <div class="col-md-5">
                <label class="small">Bahan Baku</label>
                <select id="manual_bahan_id" class="form-control form-control-sm"></select>
            </div>
            <div class="col-md-3">
                <label class="small">Qty (Total)</label>
                <input type="number" step="any" id="manual_qty" class="form-control form-control-sm" placeholder="0">
            </div>
            <div class="col-md-2">
                 <label class="small">&nbsp;</label>
                 <button type="button" class="btn btn-secondary btn-sm btn-block" id="btnAddManual">Tambah</button>
            </div>
        </div>
    </x-modal-form>

    {{-- MODAL COMPLETE --}}
    <x-modal-form id="modalCompleteSubmission" title="Selesaikan Permintaan" action="" submitText="Konfirmasi Selesai">
        @method('PATCH')
        <div class="alert alert-info">Pastikan semua harga sudah sesuai sebelum menyelesaikan.</div>
        <div class="form-group">
            <label>Kode Permintaan</label>
            <input type="text" id="complete_kode" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label>Pilih Supplier <span class="text-danger">*</span></label>
            <select name="supplier_id" class="form-control" required>
                <option value="" disabled selected>Pilih Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL VIEW (READ ONLY) --}}
    @foreach ($submissions as $item)
        @if($item->status === 'selesai' || $item->status === 'ditolak')
            <x-modal-detail id="modalViewDetail{{ $item->id }}" size="modal-lg" title="Detail Permintaan">
                {{-- Tampilan Read Only yang sama --}}
                <table class="table table-borderless">
                    <tr><th>Kode</th><td>: {{ $item->kode }}</td></tr>
                    <tr><th>Menu</th><td>: {{ $item->menu->nama }}</td></tr>
                    <tr><th>Total Harga</th><td>: Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td></tr>
                    @if($item->supplier)
                        <tr><th>Supplier</th><td>: {{ $item->supplier->nama }}</td></tr>
                    @endif
                </table>
                 <table class="table table-bordered table-sm">
                    <thead><tr><th>Bahan</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($item->details as $d)
                            <tr>
                                <td>{{ $d->bahanBaku->nama ?? '-' }}</td>
                                <td>{{ $d->qty_digunakan }}</td>
                                <td>{{ number_format($d->subtotal_harga, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                 </table>
            </x-modal-detail>
        @endif
    @endforeach

@endsection

@push('js')
<script>
    let currentSubmissionId = null;
    let currentKitchenId = null;

    // --- 1. SETUP & UTILS ---
    function formatRupiah(num) {
        return 'Rp ' + parseFloat(num).toLocaleString('id-ID', {minimumFractionDigits: 0});
    }

    // --- 2. LOAD DATA KE MODAL EDIT ---
    $('.btnEditDetail').click(function() {
        let submissionId = $(this).data('id');
        currentSubmissionId = submissionId;
        
        // Load Header Data
        $.get("{{ route('transaction.approval.data', ':id') }}".replace(':id', submissionId), function(data) {
            $('#modal_detail_kode').text(': ' + data.kode);
            $('#modal_detail_dapur').text(': ' + data.kitchen_nama);
            $('#modal_detail_menu').text(': ' + data.menu_nama);
            $('#modal_detail_porsi').text(': ' + data.porsi);
            currentKitchenId = data.kitchen_id;

            // Load Bahan Baku Manual Dropdown (Sesuai Dapur)
            loadBahanBakuDropdown(data.kitchen_id);
            // Load Detail Table
            loadDetails(submissionId);
        });
    });

    // --- 3. LOAD TABLE DETAILS ---
    function loadDetails(id) {
        let tbody = $('#edit_bahan_tbody');
        tbody.html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');
        
        $.get("{{ route('transaction.approval.details', ':id') }}".replace(':id', id), function(details) {
            tbody.empty();
            details.forEach(function(d) {
                tbody.append(`
                    <tr data-id="${d.id}">
                        <td>${d.bahan_baku_nama}</td>
                        <td><input type="number" class="form-control form-control-sm qty-input" value="${d.qty_digunakan}" step="any"></td>
                        <td>${d.satuan}</td>
                        <td><input type="number" class="form-control form-control-sm price-dapur" value="${d.harga_dapur}"></td>
                        <td><input type="number" class="form-control form-control-sm price-mitra" value="${d.harga_mitra}"></td>
                        <td class="sub-dapur">${formatRupiah(d.subtotal_dapur)}</td>
                        <td class="sub-mitra">${formatRupiah(d.subtotal_mitra)}</td>
                        <td><button type="button" class="btn btn-danger btn-xs btnDeleteDetail" data-id="${d.id}">x</button></td>
                    </tr>
                `);
            });
        });
    }

    // --- 4. LOAD DROPDOWN BAHAN BAKU (MANUAL ADD) ---
    function loadBahanBakuDropdown(kitchenId) {
        let select = $('#manual_bahan_id');
        select.html('<option>Loading...</option>');
        $.get("{{ route('transaction.approval.bahan_baku', ':kId') }}".replace(':kId', kitchenId), function(data) {
            select.empty().append('<option value="">Pilih Bahan</option>');
            data.forEach(b => select.append(`<option value="${b.id}">${b.nama} (${b.satuan || '-'})</option>`));
        });
    }

    // --- 5. CALCULATION ON INPUT CHANGE ---
    $(document).on('input', '.qty-input, .price-dapur, .price-mitra', function() {
        let row = $(this).closest('tr');
        let qty = parseFloat(row.find('.qty-input').val()) || 0;
        let pDapur = parseFloat(row.find('.price-dapur').val()) || 0;
        let pMitra = parseFloat(row.find('.price-mitra').val()) || 0;

        row.find('.sub-dapur').text(formatRupiah(qty * pDapur));
        row.find('.sub-mitra').text(formatRupiah(qty * pMitra));
    });

    // --- 6. ADD MANUAL INGREDIENT ---
    $('#btnAddManual').click(function() {
        let bahanId = $('#manual_bahan_id').val();
        let qty = $('#manual_qty').val();
        if(!bahanId || !qty) return alert('Pilih bahan dan isi qty');

        $.post("{{ route('transaction.approval.add_manual', ':id') }}".replace(':id', currentSubmissionId), {
            _token: "{{ csrf_token() }}",
            bahan_baku_id: bahanId,
            qty_digunakan: qty
        }).done(function() {
            loadDetails(currentSubmissionId);
            $('#manual_qty').val('');
        }).fail(function(xhr) { alert(xhr.responseJSON.message); });
    });

    // --- 7. DELETE DETAIL ---
    $(document).on('click', '.btnDeleteDetail', function() {
        if(!confirm('Hapus bahan ini?')) return;
        let detailId = $(this).data('id');
        let url = "{{ route('transaction.approval.delete_detail', [':sid', ':did']) }}"
                    .replace(':sid', currentSubmissionId)
                    .replace(':did', detailId);

        $.ajax({ url: url, type: 'DELETE', data: {_token: "{{ csrf_token() }}"} })
         .done(function() { loadDetails(currentSubmissionId); });
    });

    // --- 8. SUBMIT ALL CHANGES (UPDATE HARGA) ---
    $('#modalEditDetail form').on('submit', function(e) {
        e.preventDefault();
        let details = [];
        $('#edit_bahan_tbody tr').each(function() {
            details.push({
                id: $(this).data('id'),
                qty_digunakan: $(this).find('.qty-input').val(),
                harga_dapur: $(this).find('.price-dapur').val(),
                harga_mitra: $(this).find('.price-mitra').val()
            });
        });

        $.post("{{ route('transaction.approval.update_harga', ':id') }}".replace(':id', currentSubmissionId), {
            _token: "{{ csrf_token() }}",
            details: details
        }).done(function() {
            alert('Data berhasil diperbarui');
            location.reload();
        }).fail(function() { alert('Gagal update'); });
    });

    // --- 9. COMPLETE MODAL ---
    $('.btnCompleteSubmission').click(function() {
        $('#complete_kode').val($(this).data('kode'));
        $('#modalCompleteSubmission form').attr('action', $(this).data('action'));
    });

    // --- 10. FILTER ---
    $('#filterKitchen').change(function() {
        let val = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).data('kitchen').toLowerCase().indexOf(val) > -1)
        });
    });
</script>
@endpush