@extends('adminlte::page')

@section('title', 'Daftar Biaya Operasional')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Daftar Biaya Operasional</h1>
@endsection

@section('content')


{{-- BUTTON ADD --}}

<x-notification-pop-up />

<div class="card mb-3">
    <div class="card-body">
        <div class="row">

            <div class="col-md-4">
                <label>Dapur</label>
                <select id="filterKitchen" class="form-control">
                    <option value="">Semua Dapur</option>
                    @foreach ($kitchens as $k)
                            <option value="{{ strtolower($k->nama) }}">
                                {{ $k->nama }}
                            </option>
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

<div class="card">
    <div class="card-body">

        <table class="table table-bordered table-striped" id="tableSubmission">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    {{-- <th>Total</th> --}}
                    <th>Status</th>
                    <th width="230" class="text-center">Aksi</th>
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
                    {{-- <td>Rp {{ number_format($item->total_harga, 2, ',','.') }}</td> --}}
                    <td>
                        <span class="badge badge-{{
                           $item->status === 'diterima' ? 'success' :
                           ($item->status === 'selesai' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning')))
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group-vertical btn-group-sm ">
                        {{-- DETAIL (TETAP ADA) --}}
                        <button class="btn btn-primary btn-sm"
                            data-toggle="modal"
                            data-target="#modalDetail{{ $item->id }}">
                            Detail
                        </button>
                        </div>

                        <div class="btn-group-vertical btn-group-sm">
                        @if ($item->status === 'selesai')
                            <div class="text-right">
                                <a
                                    href="{{ route('transaction.operational-approval.invoice-parent', $item->id) }}"
                                    target="_blank"
                                    class="btn btn-warning btn-sm"
                                >
                                    <i class="fas fa-print mr-1"></i> Cetak
                                </a>
                            </div>
                        @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>



<x-modal-form
    id="modalAddOperational"
    size="modal-lg"
    title="Tambah Pengajuan Operasional"
    action="#"
    submitText="Simpan"
    method="POST"
>

    {{-- HEADER ROW --}}
    <div class="d-flex align-items-center">

        {{-- KODE --}}
        <div class="form-group">
            <label>Kode</label>
            <input
                type="text"
                class="form-control"
                value="OPR001"
                readonly
                required
                style="background:#e9ecef"
            >
        </div>

        {{-- TANGGAL --}}
        <div class="form-group flex-fill ml-2">
            <label>Tanggal</label>
            <input
                type="date"
                class="form-control"
                value="{{ now()->toDateString() }}"
                required
            >
        </div>

        {{-- DAPUR --}}
        <div class="form-group flex-fill ml-2">
            <label>Dapur</label>
            <select class="form-control" required>
                <option disabled selected>Pilih Dapur</option>
                <option value="1">Dapur Pusat</option>
                <option value="2">Dapur Cabang</option>
            </select>
        </div>

    </div>

    {{-- DETAIL OPERASIONAL --}}
    <div class="form-group">
        <label>Jenis Operasional</label>
        <select class="form-control" required>
            <option disabled selected>Pilih Operasional</option>
            <option value="gas">Gas LPG</option>
            <option value="listrik">Listrik</option>
            <option value="air">Air</option>
            <option value="internet">Internet</option>
        </select>
    </div>

    <div class="form-group">
        <label>Total Biaya</label>
        <input
            type="number"
            min="0"
            class="form-control"
            placeholder="Masukkan total biaya"
            required
        >
    </div>

</x-modal-form>

{{-- =========================
     MODAL DETAIL (LOOP)
========================= --}}
@foreach($submissions as $item)
<x-modal-detail 
    id="modalDetail{{ $item->id }}"
    size="modal-lg"
    title="Detail Pengajuan: {{ $item->kode }}"
>
    {{-- 1. INFO HEADER (Tampilan Tetap Sama) --}}
    <table class="table table-borderless mb-0">
        <tr><th width="140" class="py-1">Kode</th><td class="py-1">: {{ $item->kode }}</td></tr>
        <tr><th width="140" class="py-1">Tanggal</th><td class="py-1">: {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td></tr>
        <tr><th width="140" class="py-1">Dapur</th><td class="py-1">: {{ $item->kitchen->nama ?? '-' }}</td></tr>
        <tr>
            <th width="140" class="py-1">Status Utama</th>
            <td class="py-1">
                : <span class="badge badge-{{
                    $item->status === 'diterima' ? 'success' :
                    ($item->status === 'selesai' ? 'success' :
                    ($item->status === 'diproses' ? 'info' :
                    ($item->status === 'ditolak' ? 'danger' : 'warning')))
                }}">{{ strtoupper($item->status) }}</span>
            </td>
        </tr>
    </table>

    {{-- KETERANGAN JIKA DITOLAK --}}
    @if ($item->status === 'ditolak' && $item->keterangan)
        <div class="alert alert-danger mt-2 py-2">
            <strong>Alasan Penolakan:</strong> {{ $item->keterangan }}
        </div>
    @endif

    {{-- =========================
     TOMBOL TOLAK (KHUSUS DIAJUKAN & BELUM ADA CHILD)
    ========================= --}}
    @if(
    $item->status === 'diajukan' &&
    $item->children->count() === 0
    )
        <div class="text-right mb-3">
            <button
                class="btn btn-danger btn-sm btnApproval"
                data-id="{{ $item->id }}"
                data-status="ditolak"
            >
                <i class="fas fa-times-circle mr-1"></i> Tolak Pengajuan
            </button>
        </div>
    @endif

    @if ($item->status === 'diproses')
        <div class="text-right mb-3">
            <button
                class="btn btn-success btn-md btnApproval"
                data-id="{{ $item->id }}"
                data-status="selesai"
            >
                <i class="fas fa-check-circle mr-1"></i> Selesaikan Pengajuan
            </button>
        </div>
    @endif

    <hr>

    {{-- 2. FORM PROSES APPROVAL (SPLIT ORDER) --}}
    {{-- Form ini membungkus tabel agar checkbox bisa dikirim --}}
    @if(in_array($item->status, ['diajukan', 'diproses']))
    <form action="{{ route('transaction.operational-approval.store') }}" method="POST" class="form-split-order">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $item->id }}">
        
        <div class="row align-items-end mb-2">
            <div class="col-md-8">
                <label class="font-weight-bold text-primary">Pilih Supplier untuk Barang Tercentang:</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="" selected disabled>- Pilih Supplier -</option>
                    @foreach($suppliers as $supplier)
                        @if($supplier->kitchens->contains('kode', $item->kitchen_kode))
                            <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                        @endif
                    @endforeach
                </select>
                @if($suppliers->where(fn($s)=>$s->kitchens->contains('kode', $item->kitchen_kode))->isEmpty())
                    <small class="text-danger">
                        Tidak ada Supplier yang terdaftar untuk dapur ini
                    </small>
                @endif
            </div>
            <div class="col-md-4 text-right">
                {{-- Tombol Submit ada di sini agar sebaris dengan form --}}
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check-circle"></i> Proses Approval
                </button>
            </div>
        </div>
    @endif

        {{-- 3. TABEL BARANG (Dengan Checkbox) --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2">
                <thead>
                    <tr>
                        {{-- Kolom Checkbox hanya muncul jika status belum final --}}
                        @if(in_array($item->status, ['diajukan', 'diproses']))
                        <th width="40" class="text-center">
                            <input type="checkbox" class="checkAll" data-target=".item-check-{{ $item->id }}">
                        </th>
                        @endif
                        <th>Barang Operasional</th>
                        <th width="80" class="text-center">Qty</th>
                        <th class="text-right">Harga</th>
                        {{-- <th class="text-right">Subtotal</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($item->details as $detail)
                        {{-- Logic Checkbox: Disable jika barang ini sudah masuk ke child (sudah diproses) --}}
                        @php
                            // Cek manual sederhana: apakah item ini sudah ada di children?
                            // Kita asumsikan logic controller sebelumnya handle duplikasi, 
                            // tapi visual helper di sini bagus.
                            // (Opsional, kalau berat query bisa dihapus)
                            $isProcessed = false; 
                            /* $isProcessed = \App\Models\submissionOperationalDetails::whereHas('submission', function($q) use($item){
                                $q->where('parent_id', $item->id);
                            })->where('operational_id', $detail->operational_id)->exists(); 
                            */
                        @endphp

                        <tr>
                            @if(in_array($item->status, ['diajukan', 'diproses']))
                            <td class="text-center align-middle">
                                <input 
                                    type="checkbox" 
                                    name="items[]" 
                                    value="{{ $detail->id }}" 
                                    class="item-check-{{ $item->id }}"
                                    {{ $isProcessed ? 'disabled checked' : '' }}
                                >
                            </td>
                            @endif
                            <td>
                                {{ $detail->operational->nama ?? '-' }}<br>
                                <small class="text-muted">{{ $detail->keterangan }}</small>
                            </td>
                            <td class="text-center">{{ $detail->qty }}</td>
                            <td class="text-right">Rp {{ number_format($detail->harga_satuan, 2, ',', '.') }}</td>
                            {{-- <td class="text-right">Rp {{ number_format($detail->subtotal, 2, ',', '.') }}</td> --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Tidak ada detail</td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Footer Total --}}
                {{-- <tfoot>
                    <tr>
                        <th colspan="{{ in_array($item->status, ['diajukan', 'diproses']) ? '4' : '3' }}" class="text-right">Total Keseluruhan</th>
                        <th class="text-right">Rp {{ number_format($item->total_harga, 2, ',', '.') }}</th>
                    </tr>
                </tfoot> --}}
            </table>
        </div>
        
    @if(in_array($item->status, ['diajukan', 'diproses']))
    </form> {{-- Tutup Form --}}
    @endif

    {{-- 4. RIWAYAT APPROVAL (CHILDREN) --}}
    {{-- Menampilkan list pecahan order yang sudah dibuat --}}
    @if($item->children->count() > 0)
        <div class="mt-4">
            <h6 class="font-weight-bold text-secondary border-bottom pb-2">Riwayat Approval (Split Order)</h6>
            
            @foreach($item->children as $child)
            <div class="card card-body bg-light p-3 mb-2 border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>{{ $child->kode }}</strong> 
                        <span class="text-muted mx-2">|</span> 
                        <i class="fas fa-truck"></i> {{ $child->supplier->nama ?? 'Tanpa Supplier' }}
                    </div>
                    <div>
                        <span class="badge badge-{{ $child->status == 'disetujui' ? 'success' : 'secondary' }}">
                            {{ strtoupper($child->status) }}
                        </span>
                        <span class="ml-2 font-weight-bold">
                            Rp {{ number_format($child->total_harga, 2, ',', '.') }}
                        </span>
                        {{-- =========================
                            BUTTON DELETE CHILD
                        ========================= --}}
                        @if(
                            $item->status === 'diproses' &&
                            $child->status === 'disetujui'
                        )
                            <button
                                class="btn btn-xs btn-outline-danger ml-2 btnDeleteChild"
                                data-id="{{ $child->id }}"
                                title="Hapus approval supplier"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Detail Barang di Approval ini --}}
                <ul class="mb-0 pl-3" style="font-size: 0.9em;">
                    @foreach($child->details as $cDetail)
                        <li>
                            {{ $cDetail->operational->nama ?? '-' }} 
                            ({{ $cDetail->qty }} x {{ number_format($cDetail->harga_satuan) }})
                        </li>
                    @endforeach
                </ul>

                {{-- Tombol Aksi per Child (Jika diperlukan) --}}
                <div class="text-right mt-2">
                    @if($child->status === 'disetujui')
                        <a href="{{ route('transaction.operational-submission.invoice', $child->id) }}" class="btn btn-xs btn-outline-secondary" target="_blank">
                            <i class="fas fa-print"></i> Cetak Invoice
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

</x-modal-detail>

<x-modal-form
    id="modalApprovalOperational"
    title="Konfirmasi Approval"
    action="#"
    submitText="Ya, Lanjutkan"
    method="POST"
>
    @csrf
    @method('PATCH')

    <input type="hidden" name="status" id="approval_status">

    <div class="form-group d-none" id="keterangan_wrapper">
        <label>Keterangan Penolakan</label>
        <textarea
            name="keterangan"
            class="form-control"
            placeholder="Masukkan alasan penolakan"
        ></textarea>
    </div>

    <p>
        Apakah Anda yakin ingin mengubah status pengajuan ini menjadi
        <strong id="approval_status_text"></strong>?
    </p>
</x-modal-form>

@endforeach

<x-modal-detail
    id="modalSupplierRequired"
    title="Supplier Belum Dipilih"
    size="modal-md"
>
    <div class="alert alert-warning mb-3">
        <strong>Perhatian!</strong><br>
        Supplier wajib diisi sebelum pengajuan dapat disetujui.
    </div>

    <p>
        Silakan buka <strong>Detail Pengajuan</strong> lalu pilih supplier
        pada bagian <strong>Supplier</strong>.
    </p>

    <div class="text-right mt-3">
        <button class="btn btn-secondary" data-dismiss="modal">
            Tutup
        </button>
    </div>
</x-modal-detail>

<x-modal-form
    id="modalDeleteChild"
    title="Hapus Approval Supplier"
    action=""
    submitText="Ya, Hapus"
    method="POST"
>
    @csrf
    @method('DELETE')

    <p class="mb-0">
        Apakah Anda yakin ingin menghapus approval supplier ini?
    </p>
</x-modal-form>



@endsection

@push('js')
    <script>
        // Validasi minimal 1 checkbox dipilih sebelum submit form split
        $('form').on('submit', function(e){
            // Cek apakah form ini adalah form approval operational
            if($(this).find('input[name="items[]"]').length > 0) {
                if($(this).find('input[name="items[]"]:checked').length === 0) {
                    e.preventDefault();
                    alert('Harap pilih minimal satu barang untuk diproses!');
                }
            }
        });

        $(document).on('change', '.checkAll', function() {
            let target = $(this).data('target');
            $(target).prop('checked', $(this).is(':checked'));
        });

        // 2. VALIDASI FORM SPLIT ORDER
        $(document).on('submit', '.form-split-order', function(e) {
            // Cari checkbox item di dalam form ini yang dicentang
            let checkedItems = $(this).find('input[name="items[]"]:checked');
            
            if (checkedItems.length === 0) {
                e.preventDefault(); // Batalkan submit
                alert('Harap pilih minimal satu barang untuk diproses!');
            }
        });
        
        @if(session('reopen_modal'))
            // Ambil ID dari session flash controller
            let modalId = "#modalDetail{{ session('reopen_modal') }}";
            
            // Cek apakah modalnya ada di halaman
            if($(modalId).length) {
                // Tampilkan modal
                $(modalId).modal('show');
                
                // Opsional: Beri notifikasi kecil (Toastr/Alert) jika pakai library
                // toastr.success('Data berhasil disimpan, modal dibuka kembali.');
            }
        @endif

        $(document).on('click', '.btnDeleteChild', function () {

        let id = $(this).data('id');

        let modal = $('#modalDeleteChild');

        modal.find('form').attr(
            'action',
            "{{ route('transaction.operational-approval.destroy-child', ':id') }}"
                .replace(':id', id)
        );

        modal.modal('show');
    });

        
        $(document).ready(function () {

            /**
             * ======================================================
             * LOAD MENU BERDASARKAN DAPUR
             * ======================================================
             */
            function loadMenuByKitchen(kitchenId) {

                let menuSelect = $('#menu_id');

                // tampilkan loading
                menuSelect.html('<option disabled selected>Loading...</option>');

                // generate URL route dengan parameter
                let url = "{{ route('transaction.submission.menu-by-kitchen', ':kitchen') }}";
                url = url.replace(':kitchen', kitchenId);

                $.get(url)
                    .done(function (data) {

                        menuSelect.empty();
                        menuSelect.append('<option disabled selected>Pilih Menu</option>');

                        if (data.length === 0) {
                            menuSelect.append(
                                '<option disabled>Tidak ada menu untuk dapur ini</option>'
                            );
                            return;
                        }

                        data.forEach(function (menu) {
                            menuSelect.append(
                                `<option value="${menu.id}">${menu.nama}</option>`
                            );
                        });
                    })
                    .fail(function () {
                        menuSelect.html(
                            '<option disabled selected>Gagal memuat menu</option>'
                        );
                    });
            }

            /**
             * ======================================================
             * SAAT DAPUR DIPILIH
             * ======================================================
             */
            $(document).on('change', '#kitchen_id', function () {

                let kitchenId = $(this).val();

                // reset menu
                $('#menu_id').html(
                    '<option disabled selected>Pilih dapur terlebih dahulu</option>'
                );

                if (kitchenId) {
                    loadMenuByKitchen(kitchenId);
                }
            });

            /**
             * ======================================================
             * SAAT MODAL TAMBAH DIBUKA
             * ======================================================
             */
            $('#modalAddSubmission').on('shown.bs.modal', function () {

                let kitchenId = $('#kitchen_id').val();

                if (kitchenId) {
                    loadMenuByKitchen(kitchenId);
                } else {
                    $('#menu_id').html(
                        '<option disabled selected>Pilih dapur terlebih dahulu</option>'
                    );
                }
            });

            /**
             * ======================================================
             * SAAT MODAL DITUTUP → RESET FORM
             * ======================================================
             */
            $('#modalAddSubmission').on('hidden.bs.modal', function () {
                $('#kitchen_id').val('');
                $('#menu_id').html(
                    '<option disabled selected>Pilih dapur terlebih dahulu</option>'
                );
            });

        });

        function applyFilter() {
            let kitchen = ($('#filterKitchen').val() || '').toLowerCase();
            let status  = ($('#filterStatus').val() || '').toLowerCase();
            let date    = $('#filterDate').val();

            $('#tableSubmission tbody tr').each(function () {
                let rowKitchen = ($(this).data('kitchen') || '').toLowerCase();
                let rowStatus  = ($(this).data('status') || '').toLowerCase();
                let rowDate    = $(this).data('date') || '';

                let show = true;

                if (kitchen && rowKitchen !== kitchen) show = false;
                if (status && rowStatus !== status) show = false;
                if (date && rowDate !== date) show = false;

                $(this).toggle(show);
            });
        }
        $('#filterKitchen, #filterStatus, #filterDate').on('change', applyFilter);


        $(document).on('click', '.btnEdit', function () {

            let action = $(this).data('action');
            let status = $(this).data('status');

            let modal = $('#modalEditSubmission');

            modal.find('form').attr('action', action);

            let statusSelect = $('#edit_status');
            statusSelect.val(status);
            statusSelect.find('option').prop('disabled', false);

            // RULE:
            // jika status = diproses → hanya boleh diterima
            if (status === 'diproses') {
                statusSelect.find('option').prop('disabled', true);
                statusSelect.find('option[value="diterima"]').prop('disabled', false);
                statusSelect.val('diterima');
            }
        });

        /**
         * =========================
         * APPROVAL HANDLER
         * =========================
         */
        $(document).on('click', '.btnApproval', function () {

        let id     = $(this).data('id');
        let status = $(this).data('status');

        let approvalModal = $('#modalApprovalOperational');

        // cari modal detail terdekat (yang sedang terbuka)
        let detailModal = $(this).closest('.modal');

        // set action form
        approvalModal.find('form').attr(
            'action',
            "{{ route('transaction.operational-approval.update-status', ':id') }}"
                .replace(':id', id)
        );

        if (status === 'ditolak') {
            $('#keterangan_wrapper').removeClass('d-none');
        } else {
            $('#keterangan_wrapper').addClass('d-none');
            $('textarea[name="keterangan"]').val('');
        }

        $('#approval_status').val(status);
        $('#approval_status_text').text(status.toUpperCase());

        // ============================
        // TUTUP MODAL DETAIL DULU
        // ============================
        detailModal.modal('hide');

        // setelah modal detail tertutup → buka approval
        detailModal.one('hidden.bs.modal', function () {
            approvalModal.modal('show');
        });
    });



    </script>



@endpush
