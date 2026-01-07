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
                    @foreach ($submissions->pluck('kitchen')->unique('id') as $kitchen)
                        @if($kitchen)
                            <option value="{{ strtolower($kitchen->nama) }}">
                                {{ $kitchen->nama }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="diproses">Diproses</option>
                    <option value="diterima">Diterima</option>
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

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    {{-- <th>Operasional</th> --}}
                    <th>Total</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
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
                    {{-- <td>{{ $item->details->first()->operational->nama ?? '-' }}</td> --}}
                    <td>Rp {{ number_format($item->total, 0, ',','.') }}</td>
                    <td>
                        <span class="badge badge-{{
                           $item->status === 'diterima' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning'))
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td>
                        {{-- DETAIL (TETAP ADA) --}}
                        <button class="btn btn-primary btn-sm"
                            data-toggle="modal"
                            data-target="#modalDetail{{ $item->id }}">
                            Detail
                        </button>

                        {{-- =========================
                        OPERATIONAL APPROVAL
                        ========================= --}}
                        @if ($item->status === 'diajukan')
                            <button
                                class="btn btn-success btn-sm btnApproval"
                                data-id="{{ $item->id }}"
                                data-status="diterima"
                            >
                                Setujui
                            </button>

                            <button
                                class="btn btn-danger btn-sm btnApproval"
                                data-id="{{ $item->id }}"
                                data-status="ditolak"
                            >
                                Tolak
                            </button>

                            {{-- Hapus hanya boleh saat diajukan --}}
                            {{-- <x-button-delete
                                idTarget="#modalDeleteOperational"
                                formId="formDeleteOperational"
                                action="#"
                                text="Hapus"
                            /> --}}

                        @elseif ($item->status === 'diproses')
                            <button
                                class="btn btn-info btn-sm btnApproval"
                                data-id="{{ $item->id }}"
                                data-status="diterima"
                            >
                                Terima
                            </button>
                        @endif
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
MODAL DETAIL
========================= --}}
@foreach($submissions as $item)
<x-modal-detail 
    id="modalDetail{{ $item->id }}"
    size="modal-lg"
    title="Detail {{ $item->kode }}"
>

    <table class="table table-borderless">
        <tr><th>Kode</th><td>: {{ $item->kode }}</td></tr>
        <tr><th>Tanggal</th><td>: {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td></tr>
        <tr><th>Dapur</th><td>: {{ $item->kitchen->nama ?? '-' }}</td></tr>
        <tr>
            <th>Status</th>
            <td>
                :
                <span class="badge badge-{{
                    $item->status === 'diterima' ? 'success' :
                    ($item->status === 'diproses' ? 'info' :
                    ($item->status === 'ditolak' ? 'danger' : 'warning'))
                }}">
                    {{ strtoupper($item->status) }}
                </span>

                {{-- KETERANGAN DITOLAK --}}
                
            </td>
        </tr>
        <tr>@if ($item->status === 'ditolak' && $item->keterangan)
                <div class="mt-2 p-2 border rounded bg-light">
                    <large class="text-danger font-weight-bold">
                        Alasan Penolakan:
                    </large>
                    <div class="text-strong">
                        {{ $item->keterangan }}
                    </div>
                </div>
            @endif
        </tr>

    </table>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Operasional</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Keterangan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($item->details as $detail)
                <tr>
                    <td>{{ $detail->operational->nama ?? '-' }}</td>
                    <td>{{ $detail->qty }}</td>
                    <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td>{{ $detail->keterangan ?? '-' }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Tidak ada detail operasional
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th>Rp {{ number_format($item->total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

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
            let kitchen = ($('#filterKitchen').val() || '').toLowerCase();
            let status  = ($('#filterStatus').val() || '').toLowerCase();
            let date    = $('#filterDate').val();

            $('tbody tr').each(function () {
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

            let modal = $('#modalApprovalOperational');

            // endpoint approval (nanti sesuaikan route)
            modal.find('form').attr(
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

            modal.modal('show');
        });


    </script>



@endpush
