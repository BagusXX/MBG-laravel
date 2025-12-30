@extends('adminlte::page')

@section('title', $mode === 'pengajuan' ? 'Pengajuan Menu' : 'Daftar Permintaan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>{{ $mode === 'pengajuan' ? 'Pengajuan Menu' : 'Daftar Permintaan' }}</h1>
@endsection

@section('content')

    {{-- TOMBOL TAMBAH (HANYA MODE PENGAJUAN) --}}
    @if($mode === 'pengajuan')
        <x-button-add idTarget="#modalAddSubmission" text="Tambah Pengajuan Menu" />
    @endif

    <x-notification-pop-up />

    <div class="card">
        <div class="card-body">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">

                        {{-- FILTER DAPUR --}}
                        <div class="col-md-3">
                            <label>Dapur</label>
                            <select id="filterKitchen" class="form-control">
                                <option value="">Semua Dapur</option>
                                @foreach ($kitchens as $kitchen)
                                    <option value="{{ $kitchen->nama }}">{{ $kitchen->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FILTER MENU --}}
                        <div class="col-md-3">
                            <label>Menu</label>
                            <select id="filterMenu" class="form-control">
                                <option value="">Semua Menu</option>
                                @foreach ($submissions->pluck('menu.nama')->unique() as $menu)
                                    <option value="{{ $menu }}">{{ $menu }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FILTER STATUS --}}
                        <div class="col-md-3">
                            <label>Status</label>
                            <select id="filterStatus" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="diproses">Diproses</option>
                                <option value="diterima">Diterima</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>

                        {{-- FILTER TANGGAL --}}
                        <div class="col-md-3">
                            <label>Tanggal</label>
                            <input type="date" id="filterDate" class="form-control">
                        </div>

                    </div>
                </div>
            </div>

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
                                <tr data-kitchen="{{ $item->kitchen->nama }}" data-menu="{{ $item->menu->nama }}"
                                    data-status="{{ $item->status }}" data-date="{{ $item->tanggal }}">
                                    <td>{{ $item->kode }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                    <td>{{ $item->kitchen->nama }}</td>
                                    <td>{{ $item->menu->nama }}</td>
                                    <td>{{ $item->porsi }}</td>
                                    <td>
                                        <span
                                            class="badge badge-{{
                        $item->status === 'diterima' ? 'success' :
                        ($item->status === 'ditolak' ? 'danger' :
                            ($item->status === 'diproses' ? 'info' : 'warning'))
                                                                                                                                                                        }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- DETAIL (SEMUA MODE) --}}
                                        <a href="{{ route('transaction.submission.detail', $item->id) }}" class="btn btn-info btn-sm">
                                            Detail
                                        </a>

                                        {{-- MODE PERMINTAAN --}}
                                        @if($mode === 'permintaan')

                                            {{-- EDIT --}}
                                            @if($item->status !== 'diterima')
                                                <button class="btn btn-warning btn-sm btnEdit"
                                                    data-action="{{ route('transaction.submission.update', $item->id) }}"
                                                    data-status="{{ $item->status }}" data-toggle="modal" data-target="#modalEditSubmission">
                                                    Edit
                                                </button>
                                            @endif


                                            {{-- HAPUS (KECUALI DIPROSES) --}}
                                            @if($item->status !== 'diproses')
                                                <x-button-delete idTarget="#modalDeleteSubmission" formId="formDeleteSubmission"
                                                    action="{{ route('transaction.submission.destroy', $item->id) }}" text="Hapus" />
                                            @endif

                                        @endif
                                    </td>
                                </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $submissions->links('pagination::bootstrap-4') }}
            </div>

        </div>
    </div>

    {{-- =========================
    MODAL TAMBAH (PENGAJUAN)
    ========================= --}}
    @if($mode === 'pengajuan')
        <x-modal-form id="modalAddSubmission" title="Tambah Pengajuan Menu" action="{{ route('transaction.submission.store') }}"
            submitText="Simpan">

            <div class="form-group">
                <label>Kode</label>
                <input type="text" class="form-control" value="{{ $nextKode }}" readonly style="background:#e9ecef">
            </div>

            <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}">

            <div class="form-group">
                <label>Dapur</label>
                <select name="kitchen_id" id="kitchen_id" class="form-control" required>
                    <option disabled selected>Pilih Dapur</option>
                    @foreach($kitchens as $kitchen)
                        <option value="{{ $kitchen->id }}">{{ $kitchen->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Menu</label>
                <select name="menu_id" id="menu_id" class="form-control" required>
                    <option disabled selected>Pilih dapur terlebih dahulu</option>
                </select>
            </div>

            <div class="form-group">
                <label>Porsi</label>
                <input type="number" name="porsi" min="1" class="form-control" required>
            </div>
        </x-modal-form>
    @endif

    {{-- =========================
    MODAL EDIT (PERMINTAAN)
    ========================= --}}
    @if($mode === 'permintaan')
        <x-modal-form id="modalEditSubmission" title="Edit Permintaan" action="" submitText="Update">
            @method('PUT')

            <div class="form-group">
                <label>Status</label>
                <select id="edit_status" name="status" class="form-control">
                    <option value="diajukan">Diajukan</option>
                    <option value="diproses">Diproses</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
        </x-modal-form>


        <x-modal-delete id="modalDeleteSubmission" formId="formDeleteSubmission" title="Hapus Permintaan"
            message="Yakin ingin menghapus data ini?" confirmText="Hapus" />
    @endif

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