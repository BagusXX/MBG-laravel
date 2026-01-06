@extends('adminlte::page')

@section('title', 'Pengajuan Operasional')

@section('content_header')
    <h1>Pengajuan Operasional</h1>
@endsection

@section('content')

@php
    // =========================
    // DATA STATIS
    // =========================
    $submissions = collect([
        (object)[
            'id' => 1,
            'kode' => 'OPR001',
            'tanggal' => '2026-01-05',
            'dapur' => 'Dapur Pusat',
            'operasional' => 'Gas LPG',
            'total' => 150000,
            'status' => 'diajukan',
        ],
        (object)[
            'id' => 2,
            'kode' => 'OPR002',
            'tanggal' => '2026-01-06',
            'dapur' => 'Dapur Cabang',
            'operasional' => 'Listrik',
            'total' => 300000,
            'status' => 'diproses',
        ],
    ]);

    $items = [
        ['nama' => 'Gas 12 Kg', 'unit' => 'Tabung', 'harga' => 150000],
        ['nama' => 'Token Listrik', 'unit' => 'kWh', 'harga' => 300000],
    ];
@endphp

{{-- BUTTON ADD --}}
<x-button-add idTarget="#modalAddOperational" text="Tambah Pengajuan Operasional" />
<x-notification-pop-up />

<div class="card mb-3">
    <div class="card-body">
        <div class="row">

            <div class="col-md-4">
                <label>Dapur</label>
                <select id="filterKitchen" class="form-control">
                    <option value="">Semua Dapur</option>
                    <option value="Dapur Pusat">Dapur Pusat</option>
                    <option value="Dapur Cabang">Dapur Cabang</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="diproses">Diproses</option>
                    <option value="diterima">Diterima</option>
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

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Operasional</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $item)
                <tr 
                    data-kitchen="{{ $item->dapur }}"
                    data-status="{{ $item->status }}"
                    data-date="{{ $item->tanggal }}"
                >
                    <td>{{ $item->kode }}</td>
                    <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>
                    <td>{{ $item->dapur }}</td>
                    <td>{{ $item->operasional }}</td>
                    <td>Rp {{ number_format($item->total) }}</td>
                    <td>
                        <span class="badge badge-{{
                            $item->status === 'diterima' ? 'success' :
                            ($item->status === 'diproses' ? 'info' : 'warning')
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm"
                            data-toggle="modal"
                            data-target="#modalDetail{{ $item->id }}">
                            Detail
                        </button>

                        <x-button-delete
                            idTarget="#modalDeleteOperational"
                            formId="formDeleteOperational"
                            action="#"
                            text="Hapus"
                        />
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>


{{-- =========================
MODAL TAMBAH
========================= --}}
{{-- <x-modal-form
    id="modalAddOperational"
    title="Tambah Pengajuan Operasional"
    action="{{ route('transaction.operational-submission.store') }}"
    submitText="Simpan"
>

    KODE
    <div class="form-group">
        <label>Kode</label>
        <input
            type="text"
            class="form-control"
            value="{{ $nextKodeOperasional ?? 'OPRXXX' }}"
            readonly
            style="background:#e9ecef"
        >
    </div>

    TANGGAL
    <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}">

    DAPUR
    <div class="form-group">
        <label>Dapur</label>
        <select name="kitchen_id" class="form-control" required>
            <option disabled selected>Pilih Dapur</option>
            @foreach ($kitchens as $kitchen)
                <option value="{{ $kitchen->id }}">
                    {{ $kitchen->nama }}
                </option>
            @endforeach
        </select>
    </div>

    JENIS OPERASIONAL
    <div class="form-group">
        <label>Jenis Operasional</label>
        <select name="operational_id" class="form-control" required>
            <option disabled selected>Pilih Operasional</option>
            @foreach ($operationals ?? [] as $op)
                <option value="{{ $op->id }}">
                    {{ $op->nama }}
                </option>
            @endforeach
        </select>
    </div>

    TOTAL
    <div class="form-group">
        <label>Total Biaya</label>
        <input
            type="number"
            name="total"
            min="0"
            class="form-control"
            placeholder="Masukkan total biaya"
            required
        >
    </div>

</x-modal-form> --}}

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
MODAL DETAIL
========================= --}}
@foreach($submissions as $item)
<x-modal-detail 
    id="modalDetail{{ $item->id }}"
    size="modal-lg"
    title="Detail {{ $item->kode }}"
>
    <table class="table table-borderless">
        <tr><th>Dapur</th><td>: {{ $item->dapur }}</td></tr>
        <tr><th>Operasional</th><td>: {{ $item->operasional }}</td></tr>
        <tr><th>Status</th><td>: {{ strtoupper($item->status) }}</td></tr>
    </table>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Barang</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gas 12 Kg</td>
                <td>1</td>
                <td>Tabung</td>
                <td>150.000</td>
                <td>150.000</td>
            </tr>
        </tbody>
    </table>
</x-modal-detail>
@endforeach

@endsection

@push('js')
    <script>
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
            let kitchen = $('#filterKitchen').val().toLowerCase();
            let menu = $('#filterMenu').val().toLowerCase();
            let status = $('#filterStatus').val().toLowerCase();
            let date = $('#filterDate').val();

            $('tbody tr').each(function () {
                let rowKitchen = $(this).data('kitchen')?.toLowerCase() || '';
                let rowMenu = $(this).data('menu')?.toLowerCase() || '';
                let rowStatus = $(this).data('status')?.toLowerCase() || '';
                let rowDate = $(this).data('date') || '';

                let show = true;

                if (kitchen && rowKitchen !== kitchen) show = false;
                if (menu && rowMenu !== menu) show = false;
                if (status && rowStatus !== status) show = false;
                if (date && rowDate !== date) show = false;

                $(this).toggle(show);
            });
        }

        $('#filterKitchen, #filterMenu, #filterStatus, #filterDate').on('change', applyFilter);


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



    </script>



@endpush
