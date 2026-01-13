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
                                <option value="selesai">Selesai</option>
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
                                                $item->status === 'selesai' ? 'success' :
                                                ($item->status === 'ditolak' ? 'danger' :
                                                ($item->status === 'diproses' ? 'info' : 'warning'))
                                            }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($mode === "pengajuan")
                                            <button 
                                                type="button"
                                                class="btn btn-primary btn-sm btnViewDetail"
                                                data-toggle="modal"
                                                data-target="#modalViewDetail{{ $item->id }}"
                                            >
                                                Detail
                                            </button>
                                        @endif

                                        @if($mode === 'pengajuan' && $item->status === 'diajukan')
                                            <button
                                                type="button"
                                                class="btn btn-warning btn-sm btnEditSubmission"
                                                data-id="{{ $item->id }}"
                                                data-update-url="{{ route('transaction.submission.update', $item->id) }}"
                                                data-kode="{{ $item->kode }}"
                                                data-kitchen-id="{{ $item->kitchen_id }}"
                                                data-kitchen-nama="{{ $item->kitchen->nama }}"
                                                data-menu-id="{{ $item->menu_id }}"
                                                data-menu-nama="{{ $item->menu->nama }}"
                                                data-porsi="{{ $item->porsi }}"
                                                data-toggle="modal"
                                                data-target="#modalEditSubmission"
                                            >
                                                Edit
                                            </button>
                                            <x-button-delete
                                                idTarget="#modalDeleteSubmission"
                                                formId="formDeleteSubmission"
                                                action="{{ route('transaction.submission.destroy', $item->id) }}"
                                                text="Hapus"
                                            />
                                        @endif

                                        {{-- DETAIL (SEMUA MODE) --}}
                                        @if($mode === 'permintaan' && $item->status !== 'selesai')
                                            <button 
                                                type="button"
                                                class="btn btn-primary btn-sm btnEditDetail"
                                                data-toggle="modal"
                                                data-target="#modalEditDetail"
                                                data-id="{{ $item->id }}"
                                                data-kode="{{ $item->kode }}"
                                                data-tanggal="{{ $item->tanggal }}"
                                                data-kitchen-id="{{ $item->kitchen_id }}"
                                                data-menu-id="{{ $item->menu_id }}"
                                                data-porsi="{{ $item->porsi }}"
                                                data-action="{{ route('transaction.submission.update', $item->id) }}"
                                            >
                                                Detail
                                            </button>
                                        @endif

                                        {{-- DETAIL UNTUK STATUS SELESAI (READ ONLY) --}}
                                        @if($mode === 'permintaan' && $item->status === 'selesai')
                                            <button 
                                                type="button"
                                                class="btn btn-primary btn-sm btnViewDetail"
                                                data-toggle="modal"
                                                data-target="#modalViewDetail{{ $item->id }}"
                                            >
                                                Detail
                                            </button>
                                        @endif

                                        {{-- MODE PERMINTAAN --}}
                                        @if($mode === 'permintaan')

                                            {{-- TOMBOL KE PROSES --}}
                                            @if($item->status !== 'selesai' && $item->status !== 'diproses')
                                                <form 
                                                    action="{{ route('transaction.submission.to-process', $item->id) }}" 
                                                    method="POST" 
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin mengubah status menjadi diproses?')"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-warning btn-sm">
                                                        Proses
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- TOMBOL SELESAI --}}
                                            @if($item->status === 'diproses')
                                                <button 
                                                    type="button"
                                                    class="btn btn-success btn-sm btnCompleteSubmission"
                                                    data-toggle="modal"
                                                    data-target="#modalCompleteSubmission"
                                                    data-id="{{ $item->id }}"
                                                    data-kode="{{ $item->kode }}"
                                                    data-tanggal="{{ $item->tanggal }}"
                                                    data-kitchen="{{ $item->kitchen->nama }}"
                                                    data-menu="{{ $item->menu->nama }}"
                                                    data-porsi="{{ $item->porsi }}"
                                                    data-action="{{ route('transaction.submission.to-complete', $item->id) }}"
                                                >
                                                    Selesai
                                                </button>
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

    {{-- MODAL EDIT PENGAJUAN --}}
    <x-modal-form 
        id="modalEditSubmission" 
        title="Edit Pengajuan" 
        action="" 
        submitText="Perbarui"
    >
        @method('PUT')

        <div class="form-group">
            <label>Kode</label>
            <input id="editKodePengajuan" type="text" class="form-control" name="kode" readonly />
        </div>

        <input id="editKitchenId" type="hidden" name="kitchen_id" />
        <div class="form-group">
            <label>Dapur</label>
            <input id="editKitchenNama" type="text" class="form-control" readonly />
        </div>
        
        <input id="editMenuId" type="hidden" name="menu_id" />
        <div class="form-group">
            <label>Menu</label>
            <input id="editMenuNama" type="text" class="form-control" readonly />
        </div>

        <div class="form-group">
            <label>Porsi</label>
            <input id="editPorsi" type="number" class="form-control" name="porsi" required />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE PENGAJUAN --}}
    @if ($mode === 'pengajuan')
        <x-modal-delete
            id="modalDeleteSubmission"
            formId="formDeleteSubmission"
            title="Konfirmasi Hapus"
            message="Apakah Anda yakin ingin menghapus data ini?"
            confirmText="Hapus"
        />
    @endif

    {{-- MODAL EDIT DETAIL (PERMINTAAN) --}}
    @if($mode === 'permintaan')
        <x-modal-form id="modalEditDetail" size="modal-xl" title="Edit Detail Permintaan" action="" submitText="Update">
            @method('PUT')

            <table class="table table-borderless">
                <tr>
                    <th width="140" class="py-1 pl-0">Kode</th>
                    <td class="py-1" id="modal_detail_kode">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Tanggal</th>
                    <td class="py-1" id="modal_detail_tanggal">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Dapur</th>
                    <td class="py-1" id="modal_detail_dapur">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Menu</th>
                    <td class="py-1" id="modal_detail_menu">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Porsi</th>
                    <td class="py-1" id="modal_detail_porsi">: -</td>
                </tr>
            </table>
            <hr>
            <h6 class="font-weight-bold mb-3">Detail Bahan Baku</h6>
            <div id="edit_bahan_baku_list" class="table-responsive mb-3">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Qty Digunakan</th>
                            <th>Satuan</th>
                            <th>Harga Dapur</th>
                            <th>Harga Mitra</th>
                            <th>Subtotal Dapur</th>
                            <th>Subtotal Mitra</th>
                            <th width="80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="edit_bahan_tbody">
                        <tr>
                            {{-- <td colspan="8" class="text-center text-muted">Pilih menu untuk melihat detail bahan baku</td> --}}
                        </tr>
                    </tbody>
                </table>
            </div>
            <hr class="my-4">
            <h6 class="font-weight-bold mb-3">Tambah Bahan Baku Manual</h6>
            <div class="form-group">
                <div class="form-row mb-2 small text-muted font-weight-bold">
                    <div class="col-md-5">Bahan Baku</div>
                    <div class="col-md-3">Jumlah</div>
                    <div class="col-md-4">Satuan</div>
                </div>

                <div id="tambah-bahan-wrapper">
                    {{-- Template Row Pertama --}}
                    <div class="form-row mb-3 bahan-tambah-group">
                        <div class="col-md-5">
                            <select name="tambah_bahan_baku_id[]" class="form-control bahan-tambah-select">
                                <option value="" disabled selected>Pilih Bahan</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <input type="number" step="any" min="0" name="tambah_recipe_jumlah[]" class="form-control" placeholder="Jumlah per porsi">
                            <small class="text-muted">Jumlah per porsi (akan dikalikan dengan porsi)</small>
                        </div>

                        <div class="col-md-4">
                            <input type="text" class="form-control satuan-tambah-text bg-light" placeholder="-" readonly>
                        </div>
                    </div>
                </div>
            </div>
            
        </x-modal-form>
    @endif

    {{-- MODAL KONFIRMASI SELESAI (PERMINTAAN) --}}
    @if($mode === 'permintaan')
        <x-modal-form id="modalCompleteSubmission" size="modal-xl" title="Konfirmasi Selesai" action="" submitText="Konfirmasi Selesai">
            @method('PATCH')
            @csrf

            {{-- DETAIL SUBMISSION --}}
            <h6 class="font-weight-bold mb-3">Detail Permintaan</h6>
            <table class="table table-borderless mb-4">
                <tr>
                    <th width="140" class="py-1 pl-0">Kode</th>
                    <td class="py-1" id="complete_kode">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Tanggal</th>
                    <td class="py-1" id="complete_tanggal">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Dapur</th>
                    <td class="py-1" id="complete_kitchen">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Menu</th>
                    <td class="py-1" id="complete_menu">: -</td>
                </tr>
                <tr>
                    <th width="140" class="py-1 pl-0">Porsi</th>
                    <td class="py-1" id="complete_porsi">: -</td>
                </tr>
            </table>

            <hr>

            {{-- DETAIL BAHAN BAKU --}}
            <h6 class="font-weight-bold mb-3">Detail Bahan Baku</h6>
            <div id="complete_bahan_baku_list" class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Qty Digunakan</th>
                            <th>Satuan</th>
                            <th>Harga Dapur</th>
                            <th>Harga Mitra</th>
                            <th>Subtotal Dapur</th>
                            <th>Subtotal Mitra</th>
                        </tr>
                    </thead>
                    <tbody id="complete_bahan_tbody">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr>

            {{-- PILIH SUPPLIER --}}
            <div class="form-group">
                <label class="font-weight-bold">Pilih Supplier <span class="text-danger">*</span></label>
                <select name="supplier_id" id="complete_supplier_id" class="form-control" required>
                    <option value="" disabled selected>Pilih Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama }} - {{ $supplier->kode }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih supplier yang akan menangani permintaan ini</small>
            </div>
        </x-modal-form>
    @endif

    {{-- MODAL VIEW DETAIL (READ ONLY) --}}
    @foreach ($submissions as $item)
        @if($mode === 'pengajuan' || ($mode === 'permintaan' && $item->status === 'selesai'))
            <x-modal-detail id="modalViewDetail{{ $item->id }}" size="modal-lg" title="Detail Pengajuan Menu">
                <table class="table table-borderless">
                    <tr>
                        <th width="140" class="py-2">Kode</th>
                        <td class="py-2">: {{ $item->kode }}</td>
                    </tr>
                    <tr>
                        <th width="140" class="py-2">Tanggal</th>
                        <td class="py-2">: {{ date('d-m-Y', strtotime($item->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <th width="140" class="py-2">Dapur</th>
                        <td class="py-2">: {{ $item->kitchen->nama }}</td>
                    </tr>
                    <tr>
                        <th width="140" class="py-2">Menu</th>
                        <td class="py-2">: {{ $item->menu->nama }}</td>
                    </tr>
                    <tr>
                        <th width="140" class="py-2">Porsi</th>
                        <td class="py-2">: {{ $item->porsi }}</td>
                    </tr>
                    @if($mode === 'permintaan' && $item->supplier)
                        <tr>
                            <th width="140" class="py-2">Supplier</th>
                            <td class="py-2">: {{ $item->supplier->nama }} ({{ $item->supplier->kode }})</td>
                        </tr>
                        {{-- <tr>
                            <th width="140" class="py-2">Kontak Supplier</th>
                            <td class="py-2">: {{ $item->supplier->kontak }} - {{ $item->supplier->nomor }}</td>
                        </tr> --}}
                    @endif
                </table>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Qty Digunakan</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($item->details as $detail)
                            @php
                                $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
                                $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
                                $subtotalDapur = $hargaDapur * $detail->qty_digunakan;
                                $subtotalMitra = $hargaMitra * $detail->qty_digunakan;
                            @endphp
                            <tr>
                                <td>{{ $detail->recipe?->bahan_baku?->nama ?? $detail->bahanBaku?->nama ?? '-' }}</td>
                                <td>{{ number_format($detail->qty_digunakan, 2, ',', '.') }}</td>
                                <td>{{ $detail->recipe?->bahan_baku?->unit?->satuan ?? $detail->bahanBaku?->unit?->satuan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Data bahan baku tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-modal-detail>
        @endif
    @endforeach

@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btnEditSubmission').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;

                    const form = document.querySelector('#modalEditSubmission form');
                    form.action = this.dataset.updateUrl;

                    document.getElementById('editKodePengajuan').value = this.dataset.kode;

                    document.getElementById('editKitchenId').value = this.dataset.kitchenId;
                    document.getElementById('editKitchenNama').value = this.dataset.kitchenNama;

                    document.getElementById('editMenuId').value = this.dataset.menuId;
                    document.getElementById('editMenuNama').value = this.dataset.menuNama;

                    document.getElementById('editPorsi').value = this.dataset.porsi;
                });
            });
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

        /**
         * ======================================================
         * LOAD MENU UNTUK MODAL EDIT DETAIL
         * ======================================================
         */
        function loadMenuByKitchenForEdit(kitchenId, selectedMenuId = null) {
            let menuSelect = $('#edit_menu_id');
            menuSelect.html('<option disabled selected>Loading...</option>');

            let url = "{{ route('transaction.submission.menu-by-kitchen', ':kitchen') }}";
            url = url.replace(':kitchen', kitchenId);

            $.get(url)
                .done(function (data) {
                    menuSelect.empty();
                    menuSelect.append('<option disabled selected>Pilih Menu</option>');

                    if (data.length === 0) {
                        menuSelect.append('<option disabled>Tidak ada menu untuk dapur ini</option>');
                        return;
                    }

                    data.forEach(function (menu) {
                        let selected = (menu.id == selectedMenuId) ? 'selected' : '';
                        menuSelect.append(
                            `<option value="${menu.id}" ${selected}>${menu.nama}</option>`
                        );
                    });

                    // Load detail bahan baku jika menu sudah dipilih
                    // if (selectedMenuId) {
                    //     loadBahanBakuByMenu(selectedMenuId);
                    // }
                })
                .fail(function () {
                    menuSelect.html('<option disabled selected>Gagal memuat menu</option>');
                });
        }

        /**
         * ======================================================
         * FORMAT NUMBER KE RUPIAH
         * ======================================================
         */
        function formatRupiah(number) {
            return 'Rp ' + parseFloat(number).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }

        /**
         * ======================================================
         * LOAD DETAIL BAHAN BAKU DARI SUBMISSION
         * ======================================================
         */
        let bahanBakuList = []; // Simpan list bahan baku untuk dropdown

        function loadSubmissionDetails(submissionId, kitchenId = null) {
            let tbody = $('#edit_bahan_tbody');
            tbody.html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');

            let url = "{{ route('transaction.submission.details', ':id') }}";
            url = url.replace(':id', submissionId);

            $.get(url)
                .done(function (data) {
                    tbody.empty();

                    if (data.length === 0) {
                        tbody.html('<tr><td colspan="8" class="text-center text-muted">Data bahan baku tidak ditemukan</td></tr>');
                        return;
                    }

                    // Load bahan baku untuk dropdown jika kitchenId tersedia
                    if (kitchenId) {
                        loadBahanBakuForEdit(kitchenId, function() {
                            renderDetailRows(data, tbody);
                            // Auto-populate dropdown setelah render
                            setTimeout(function() {
                                populateBahanBakuDropdowns();
                            }, 100);
                        });
                    } else {
                        // Langsung render jika kitchenId tidak tersedia
                        renderDetailRows(data, tbody);
                        // Auto-populate dropdown setelah render
                        setTimeout(function() {
                            populateBahanBakuDropdowns();
                        }, 100);
                    }
                })
                .fail(function () {
                    tbody.html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data bahan baku</td></tr>');
                });
        }

        /**
         * ======================================================
         * RENDER DETAIL ROWS
         * ======================================================
         */
        function renderDetailRows(data, tbody) {
            // Cek apakah mode permintaan (untuk membuat qty dan bahan baku readonly)
            let isPermintaanMode = {{ $mode === 'permintaan' ? 'true' : 'false' }};
            
            data.forEach(function (detail) {
                let qtyReadonlyAttr = isPermintaanMode ? 'readonly style="background:#e9ecef"' : '';
                let bahanBakuDisabledAttr = isPermintaanMode ? 'disabled style="background:#e9ecef; cursor:not-allowed"' : '';
                let bahanBakuNama = detail.bahan_baku_nama || '-';
                
                // Untuk mode permintaan, tampilkan sebagai text, bukan dropdown
                let bahanBakuCell = '';
                if (isPermintaanMode) {
                    bahanBakuCell = `<td class="bahan-baku-cell">
                        <input type="text" class="form-control form-control-sm" value="${bahanBakuNama}" readonly style="background:#e9ecef; border:none;">
                        <input type="hidden" class="bahan-baku-select" data-detail-id="${detail.id}" data-current-bahan-id="${detail.bahan_baku_id || ''}" value="${detail.bahan_baku_id || ''}">
                    </td>`;
                } else {
                    bahanBakuCell = `<td class="bahan-baku-cell">
                        <select class="form-control form-control-sm bahan-baku-select" data-detail-id="${detail.id}" data-current-bahan-id="${detail.bahan_baku_id || ''}">
                            <option value="">Pilih Bahan Baku</option>
                        </select>
                    </td>`;
                }
                
                tbody.append(`
                    <tr data-detail-id="${detail.id}" data-bahan-baku-id="${detail.bahan_baku_id || ''}">
                        ${bahanBakuCell}
                        <td class="qty-cell">
                            <input type="number" class="form-control form-control-sm qty-input" value="${detail.qty_digunakan}" min="0" step="any" ${qtyReadonlyAttr}>
                        </td>
                        <td class="satuan-cell">${detail.satuan}</td>
                        <td class="harga-dapur-cell">
                            <input type="number" class="form-control form-control-sm harga-dapur-input" value="${detail.harga_dapur}" min="0" step="0.01">
                        </td>
                        <td class="harga-mitra-cell">
                            <input type="number" class="form-control form-control-sm harga-mitra-input" value="${detail.harga_mitra}" min="0" step="0.01">
                        </td>
                        <td class="subtotal-dapur-cell">${formatRupiah(detail.subtotal_dapur)}</td>
                        <td class="subtotal-mitra-cell">${formatRupiah(detail.subtotal_mitra)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm btn-hapus-bahan" data-detail-id="${detail.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Populate dropdown bahan baku
            populateBahanBakuDropdowns();
        }

        /**
         * ======================================================
         * LOAD BAHAN BAKU UNTUK DROPDOWN EDIT
         * ======================================================
         */
        function loadBahanBakuForEdit(kitchenId, callback) {
            if (!kitchenId) {
                if (callback) callback();
                return;
            }

            let url = "{{ route('transaction.submission.bahan-baku-by-kitchen', ':kitchen') }}";
            url = url.replace(':kitchen', kitchenId);

            $.get(url)
                .done(function(data) {
                    bahanBakuList = data;
                    if (callback) callback();
                })
                .fail(function() {
                    bahanBakuList = [];
                    if (callback) callback();
                });
        }

        /**
         * ======================================================
         * POPULATE DROPDOWN BAHAN BAKU
         * ======================================================
         */
        function populateBahanBakuDropdowns() {
            // Hanya populate select yang ada (bukan input text untuk mode permintaan)
            $('.bahan-baku-select').each(function() {
                let select = $(this);
                
                // Skip jika ini adalah hidden input (untuk mode permintaan)
                if (select.is('input[type="hidden"]')) {
                    return;
                }
                
                let currentBahanBakuId = select.data('current-bahan-id') || select.closest('tr').data('bahan-baku-id') || null;

                select.empty();
                select.append('<option value="">Pilih Bahan Baku</option>');

                if (bahanBakuList.length === 0) {
                    select.append('<option disabled>Tidak ada bahan baku</option>');
                    return;
                }

                bahanBakuList.forEach(function(bahan) {
                    let option = $('<option></option>')
                        .attr('value', bahan.id)
                        .text(bahan.nama);
                    
                    // Set selected jika sama dengan current
                    if (currentBahanBakuId && bahan.id == currentBahanBakuId) {
                        option.attr('selected', true);
                    }
                    
                    select.append(option);
                });
            });
        }

        /**
         * ======================================================
         * LOAD BAHAN BAKU BERDASARKAN MENU (DARI RECIPE)
         * ======================================================
         */
        function loadBahanBakuByMenu(menuId, kitchenId, porsi = 1) {
            console.log('LOAD DIPANGGIL', menuId, kitchenId, porsi);

            if (!menuId || !kitchenId) {
                console.warn('Menu atau Kitchen belum valid', menuId, kitchenId);
                return;
            }

            let tbody = $('#edit_bahan_tbody');
            // tbody.html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');

            // Load dari recipe
            let url = "{{ route('recipe.detail', [':menu', ':kitchen']) }}";
            url = url.replace(':menu', menuId).replace(':kitchen', kitchenId);

            $.get(url)
                .done(function (data) {
                    tbody.empty();

                    if (!Array.isArray(data) || data.length === 0) {
                        console.log(data);
                        console.log(Array.isArray(data), data.length);
                        tbody.html('<tr><td colspan="8" class="text-center text-muted">Tidak ada bahan baku untuk menu ini</td></tr>');
                        return;
                    }

                    data.forEach(function (recipe) {
                        let qty = (recipe.jumlah || 0) * porsi;
                        let hargaSatuan = recipe.bahan_baku?.harga || 0;
                        let hargaDapur = hargaSatuan;
                        let hargaMitra = hargaSatuan; // Bisa dikembangkan dengan perhitungan markup nanti
                        let subtotalDapur = hargaDapur * qty;
                        let subtotalMitra = hargaMitra * qty;

                        // tbody.append(`
                        //     <tr>
                        //         <td>${recipe.bahan_baku?.nama || '-'}</td>
                        //         <td>${parseFloat(qty).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        //         <td>${recipe.bahan_baku?.unit?.satuan || '-'}</td>
                        //         <td>${formatRupiah(hargaDapur)}</td>
                        //         <td>${formatRupiah(hargaMitra)}</td>
                        //         <td>${formatRupiah(subtotalDapur)}</td>
                        //         <td>${formatRupiah(subtotalMitra)}</td>
                        //         <td></td>
                        //     </tr>
                        // `);
                    });
                })
                .fail(function () {
                    tbody.html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data bahan baku</td></tr>');
                });
        }

        // Simpan submissionId, kitchenId, dan porsi global untuk digunakan di fungsi lain
        let currentSubmissionId = null;
        let currentKitchenId = null;
        let currentPorsi = null;

        /**
         * ======================================================
         * SAAT TOMBOL DETAIL DIKLIK (MODE PERMINTAAN)
         * ======================================================
         */
        $(document).on('click', '.btnEditDetail', function () {
            const menuId = $(this).data('menu-id');
            const kitchenId = $(this).data('kitchen-id');
            const porsi = $(this).data('porsi') || 1;

            console.log('DATA DARI BUTTON', {
                menuId,
                kitchenId,
                porsi
            });

            loadBahanBakuByMenu(menuId, kitchenId, porsi);

            let submissionId = $(this).data('id');
            currentSubmissionId = submissionId; // Simpan untuk digunakan di fungsi lain
            
            let action = $(this).data('action');

            // Set form action
            $('#modalEditDetail form').attr('action', action);

            // Load data submission lengkap dari database untuk memastikan semua data (termasuk porsi) sesuai
            let dataUrl = "{{ route('transaction.submission.data', ':id') }}";
            dataUrl = dataUrl.replace(':id', submissionId);
            
            $.get(dataUrl)
                .done(function(data) {
                    // Update tabel modal dengan data dari database
                    $('#modal_detail_kode').text(': ' + (data.kode || '-'));
                    
                    // Format tanggal
                    let tanggalFormatted = new Date(data.tanggal).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    $('#modal_detail_tanggal').text(': ' + tanggalFormatted);
                    $('#modal_detail_dapur').text(': ' + (data.kitchen_nama || '-'));
                    $('#modal_detail_menu').text(': ' + (data.menu_nama || '-'));
                    $('#modal_detail_porsi').text(': ' + (data.porsi || '-'));
                    
                    // Simpan data ke variabel global
                    currentKitchenId = data.kitchen_id;
                    currentPorsi = data.porsi;
                    
                    // Load menu berdasarkan dapur (untuk display saja, tidak bisa diubah)
                    if (data.kitchen_id) {
                        loadMenuByKitchenForEdit(data.kitchen_id, data.menu_id);
                    }

                    // Load detail bahan baku dari submission yang sudah ada - langsung dengan kitchenId
                    loadSubmissionDetails(submissionId, data.kitchen_id);
                    
                    // Reset form tambah inline
                    resetFormTambahInline();
                    
                    // Load bahan baku untuk form tambah inline
                    if (data.kitchen_id) {
                        loadBahanBakuForTambahInline(data.kitchen_id);
                    }
                })
                .fail(function() {
                    // Jika gagal load dari database, gunakan data dari data attribute sebagai fallback
                    let kode = $(this).data('kode');
                    let tanggal = $(this).data('tanggal');
                    let kitchenId = $(this).data('kitchen-id');
                    currentKitchenId = kitchenId;
                    let menuId = $(this).data('menu-id');
                    let porsi = $(this).data('porsi');
                    currentPorsi = porsi;
                    
                    $('#modal_detail_kode').text(': ' + (kode || '-'));
                    let tanggalFormatted = new Date(tanggal).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    $('#modal_detail_tanggal').text(': ' + tanggalFormatted);
                    $('#modal_detail_porsi').text(': ' + (porsi || '-'));
                    
                    if (kitchenId) {
                        loadMenuByKitchenForEdit(kitchenId, menuId);
                        loadSubmissionDetails(submissionId, kitchenId);
                        loadBahanBakuForTambahInline(kitchenId);
                    }
                    
                    resetFormTambahInline();
                }.bind(this));
        });

        /**
         * ======================================================
         * SAAT DAPUR DIUBAH DI MODAL EDIT
         * ======================================================
         */
        // Handler untuk change dapur - hanya aktif jika field tidak disabled
        $(document).on('change', '#edit_kitchen_id:not(:disabled)', function () {
            let kitchenId = $(this).val();
            if (kitchenId) {
                loadMenuByKitchenForEdit(kitchenId);
                loadBahanBakuForTambahInline(kitchenId);
                // Reload bahan baku list untuk dropdown edit
                loadBahanBakuForEdit(kitchenId, function() {
                    populateBahanBakuDropdowns();
                });
            } else {
                $('#edit_menu_id').html('<option disabled selected>Pilih dapur terlebih dahulu</option>');
                bahanBakuList = [];
            }
        });

        /**
         * ======================================================
         * SAAT MENU DIUBAH DI MODAL EDIT
         * ======================================================
         */
        // Handler untuk change menu - hanya aktif jika field tidak disabled
        $(document).on('change', '#edit_menu_id:not(:disabled)', function () {
            let menuId = $(this).val();
            let kitchenId = $('#edit_kitchen_id').val();
            let porsi = $('#edit_porsi').val() || 1;

            if (menuId && kitchenId) {
                // Load detail bahan baku berdasarkan menu dari recipe
                loadBahanBakuByMenu(menuId, kitchenId, porsi);
            }
        });

        /**
         * ======================================================
         * SAAT PORSI DIUBAH DI MODAL EDIT
         * ======================================================
         */
        // Handler untuk change porsi - hanya aktif jika field tidak readonly/disabled
        $(document).on('change', '#edit_porsi:not(:disabled):not([readonly])', function () {
            let menuId = $('#edit_menu_id').val();
            let kitchenId = $('#edit_kitchen_id').val();
            let porsi = $(this).val() || 1;

            if (menuId && kitchenId) {
                // Reload detail bahan baku dengan porsi baru
                loadBahanBakuByMenu(menuId, kitchenId, porsi);
            }
        });


        /**
         * ======================================================
         * UPDATE SUBTOTAL SAAT HARGA/QTY/BAHAN BAKU DIUBAH
         * ======================================================
         */
        $(document).on('input change', '.harga-dapur-input, .harga-mitra-input, .qty-input, .bahan-baku-select', function () {
            // Skip jika ini adalah hidden input (untuk mode permintaan)
            if ($(this).is('input[type="hidden"]')) {
                return;
            }
            
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('.qty-input').val()) || parseFloat(row.find('.qty-display').text().replace(/\./g, '').replace(',', '.')) || 0;
            let hargaDapur = parseFloat(row.find('.harga-dapur-input').val()) || 0;
            let hargaMitra = parseFloat(row.find('.harga-mitra-input').val()) || 0;
            
            // Jika bahan baku diubah (hanya untuk mode pengajuan yang masih menggunakan select)
            if ($(this).hasClass('bahan-baku-select') && $(this).is('select') && $(this).val()) {
                let bahanId = $(this).val();
                let selectedBahan = bahanBakuList.find(b => b.id == bahanId);
                if (selectedBahan) {
                    row.find('.harga-dapur-input').val(selectedBahan.harga);
                    row.find('.harga-mitra-input').val(selectedBahan.harga);
                    row.find('.satuan-cell').text(selectedBahan.satuan || '-');
                    hargaDapur = selectedBahan.harga;
                    hargaMitra = selectedBahan.harga;
                }
            }
            
            let subtotalDapur = hargaDapur * qty;
            let subtotalMitra = hargaMitra * qty;
            
            row.find('.subtotal-dapur-cell').text(formatRupiah(subtotalDapur));
            row.find('.subtotal-mitra-cell').text(formatRupiah(subtotalMitra));
        });

        /**
         * ======================================================
         * FORM SUBMIT HANDLER - UPDATE SEMUA PERUBAHAN
         * Menggabungkan simpan detail dan simpan bahan baku menjadi satu tombol Update
         * ======================================================
         */
        $('#modalEditDetail form').on('submit', function(e) {
            e.preventDefault();
            
            if (!currentSubmissionId) {
                alert('Submission ID tidak ditemukan');
                return false;
            }

            // Disable tombol submit untuk mencegah double submit
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            // Kumpulkan data detail yang diubah
            let details = [];
            let hasError = false;
            let errorMessages = [];

            let isPermintaanMode = {{ $mode === 'permintaan' ? 'true' : 'false' }};
            
            $('#edit_bahan_tbody tr[data-detail-id]').each(function () {
                let detailId = $(this).data('detail-id');
                let bahanBakuId = null;
                
                // Untuk mode permintaan, bahan baku tidak bisa diubah, gunakan yang sudah ada
                if (isPermintaanMode) {
                    // Ambil dari hidden input atau data attribute
                    let hiddenInput = $(this).find('.bahan-baku-select[type="hidden"]');
                    bahanBakuId = hiddenInput.length ? hiddenInput.val() : ($(this).data('bahan-baku-id') || null);
                } else {
                    // Untuk mode pengajuan, ambil dari select
                    let select = $(this).find('.bahan-baku-select');
                    bahanBakuId = select.is('select') ? select.val() : null;
                    // Jika tidak dipilih, gunakan bahan_baku_id yang sudah ada
                    if (!bahanBakuId || bahanBakuId === '') {
                        bahanBakuId = $(this).data('bahan-baku-id') || null;
                    }
                }
                let qtyDigunakan = parseFloat($(this).find('.qty-input').val()) || 0;
                let hargaDapur = parseFloat($(this).find('.harga-dapur-input').val()) || 0;
                let hargaMitra = parseFloat($(this).find('.harga-mitra-input').val()) || 0;
                
                if (qtyDigunakan <= 0) {
                    hasError = true;
                    errorMessages.push('Quantity harus lebih dari 0 untuk semua bahan baku');
                    return false;
                }
                
                details.push({
                    id: detailId,
                    bahan_baku_id: bahanBakuId,
                    qty_digunakan: qtyDigunakan,
                    harga_dapur: hargaDapur,
                    harga_mitra: hargaMitra
                });
            });

            if (hasError) {
                submitBtn.prop('disabled', false).html(originalText);
                alert(errorMessages.join('\n'));
                return false;
            }

            // Kumpulkan data bahan baku baru
            let bahanBakuBaru = [];
            let porsi = parseFloat(currentPorsi) || 1; // Ambil porsi dari variabel global untuk menghitung qty_digunakan
            
            $('#tambah-bahan-wrapper .bahan-tambah-group').each(function() {
                let bahanId = $(this).find('.bahan-tambah-select').val();
                // Untuk mode permintaan, input adalah recipe_jumlah (jumlah per porsi)
                let recipeJumlah = $(this).find('input[name="tambah_recipe_jumlah[]"]').val();
                
                if (bahanId && recipeJumlah && parseFloat(recipeJumlah) > 0) {
                    // Hitung qty_digunakan = recipe_jumlah * porsi
                    let qtyDigunakan = parseFloat(recipeJumlah) * porsi;
                    
                    bahanBakuBaru.push({
                        bahan_baku_id: bahanId,
                        qty_digunakan: qtyDigunakan
                    });
                }
            });

            // Validasi: minimal ada satu perubahan (detail atau bahan baru)
            if (details.length === 0 && bahanBakuBaru.length === 0) {
                submitBtn.prop('disabled', false).html(originalText);
                alert('Tidak ada perubahan yang perlu disimpan');
                return false;
            }

            // Update bahan baku yang diubah
            let updatePromise = Promise.resolve();
            if (details.length > 0) {
                updatePromise = $.ajax({
                    url: "{{ route('transaction.submission.update-harga', ':id') }}".replace(':id', currentSubmissionId),
                    method: 'PATCH',
                    data: {
                        details: details,
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
            }

            // Tambah bahan baku baru
            let addPromises = bahanBakuBaru.map(function(bahan) {
                return $.ajax({
                    url: "{{ route('transaction.submission.add-bahan-baku', ':id') }}".replace(':id', currentSubmissionId),
                    method: 'POST',
                    data: {
                        bahan_baku_id: bahan.bahan_baku_id,
                        qty_digunakan: bahan.qty_digunakan,
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
            });

            // Jalankan semua update
            Promise.all([updatePromise, ...addPromises])
                .then(function(results) {
                    // Reload data dengan kitchenId yang sudah tersimpan (realtime update)
                    loadSubmissionDetails(currentSubmissionId, currentKitchenId);
                    
                    // Reset form tambah
                    resetFormTambahInline();
                    
                    // Tampilkan notifikasi sukses tanpa menutup modal
                    let successMessages = [];
                    if (details.length > 0) {
                        successMessages.push(details.length + ' detail bahan baku berhasil diperbarui');
                    }
                    if (bahanBakuBaru.length > 0) {
                        successMessages.push(bahanBakuBaru.length + ' bahan baku baru berhasil ditambahkan');
                    }
                    
                    // Tampilkan notifikasi sukses (tidak tutup modal untuk realtime update)
                    if (successMessages.length > 0) {
                        // Tampilkan notifikasi di dalam modal atau toast
                        let notificationHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            '<strong>Berhasil!</strong> ' + successMessages.join(', ') +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button></div>';
                        
                        // Hapus notifikasi lama jika ada
                        $('#modalEditDetail .alert').remove();
                        
                        // Tambahkan notifikasi di atas form
                        $('#modalEditDetail .modal-body').prepend(notificationHtml);
                        
                        // Auto-hide setelah 3 detik
                        setTimeout(function() {
                            $('#modalEditDetail .alert').fadeOut(function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                })
                .catch(function(xhr) {
                    let message = xhr.responseJSON?.message || 'Gagal memperbarui data';
                    alert('Error: ' + message);
                })
                .finally(function() {
                    // Re-enable tombol submit
                    submitBtn.prop('disabled', false).html(originalText);
                });

            return false;
        });

        /**
         * ======================================================
         * HAPUS BAHAN BAKU
         * ======================================================
         */
        $(document).on('click', '.btn-hapus-bahan', function() {
            if (!confirm('Yakin ingin menghapus bahan baku ini?')) {
                return;
            }

            let detailId = $(this).data('detail-id');
            let submissionId = currentSubmissionId;

            if (!submissionId) {
                alert('Submission ID tidak ditemukan');
                return;
            }

            let url = "{{ route('transaction.submission.delete-detail', [':submission', ':detail']) }}";
            url = url.replace(':submission', submissionId).replace(':detail', detailId);

            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Reload data dengan kitchenId yang sudah tersimpan (realtime update)
                    loadSubmissionDetails(submissionId, currentKitchenId);
                    
                    // Tampilkan notifikasi sukses tanpa menutup modal
                    let notificationHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<strong>Berhasil!</strong> Bahan baku berhasil dihapus' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button></div>';
                    
                    // Hapus notifikasi lama jika ada
                    $('#modalEditDetail .alert').remove();
                    
                    // Tambahkan notifikasi di atas form
                    $('#modalEditDetail .modal-body').prepend(notificationHtml);
                    
                    // Auto-hide setelah 3 detik
                    setTimeout(function() {
                        $('#modalEditDetail .alert').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Gagal menghapus bahan baku';
                    alert(message);
                }
            });
        });

        /**
         * ======================================================
         * LOAD BAHAN BAKU UNTUK FORM TAMBAH INLINE
         * ======================================================
         */
        function loadBahanBakuForTambahInline(kitchenId) {
            if (!kitchenId) return;

            let url = "{{ route('transaction.submission.bahan-baku-by-kitchen', ':kitchen') }}";
            url = url.replace(':kitchen', kitchenId);

            $.get(url)
                .done(function(data) {
                    // Update semua select bahan baku di form tambah
                    $('.bahan-tambah-select').each(function() {
                        let currentValue = $(this).val();
                        $(this).empty();
                        $(this).append('<option value="" disabled selected>Pilih Bahan</option>');
                        
                        if (data.length === 0) {
                            $(this).append('<option disabled>Tidak ada bahan baku untuk dapur ini</option>');
                            return;
                        }

                        data.forEach(function(bahan) {
                            let option = $('<option></option>').attr('value', bahan.id).text(bahan.nama);
                            if (currentValue == bahan.id) {
                                option.attr('selected', true);
                            }
                            $(this).append(option);
                        }.bind(this));
                    });
                })
                .fail(function() {
                    $('.bahan-tambah-select').html('<option disabled selected>Gagal memuat bahan baku</option>');
                });
        }

        // Handler untuk tambah baris bahan baku dihapus - tidak diperlukan untuk mode permintaan

        // Handler untuk hapus baris bahan baku dihapus - tidak diperlukan untuk mode permintaan (hanya satu baris)

        /**
         * ======================================================
         * HANDLER TOMBOL SELESAI (PERMINTAAN)
         * ======================================================
         */
        $(document).on('click', '.btnCompleteSubmission', function () {
            let submissionId = $(this).data('id');
            let kode = $(this).data('kode');
            let tanggal = $(this).data('tanggal');
            let kitchen = $(this).data('kitchen');
            let menu = $(this).data('menu');
            let porsi = $(this).data('porsi');
            let action = $(this).data('action');

            // Set form action
            $('#modalCompleteSubmission form').attr('action', action);

            // Update detail di modal
            $('#complete_kode').text(': ' + kode);
            let tanggalFormatted = new Date(tanggal).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            $('#complete_tanggal').text(': ' + tanggalFormatted);
            $('#complete_kitchen').text(': ' + kitchen);
            $('#complete_menu').text(': ' + menu);
            $('#complete_porsi').text(': ' + porsi);

            // Reset supplier
            $('#complete_supplier_id').val('');

            // Load detail bahan baku
            loadCompleteSubmissionDetails(submissionId);
        });

        /**
         * ======================================================
         * LOAD DETAIL BAHAN BAKU UNTUK MODAL SELESAI
         * ======================================================
         */
        function loadCompleteSubmissionDetails(submissionId) {
            let tbody = $('#complete_bahan_tbody');
            tbody.html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

            let url = "{{ route('transaction.submission.details', ':id') }}";
            url = url.replace(':id', submissionId);

            $.get(url)
                .done(function (data) {
                    tbody.empty();

                    if (data.length === 0) {
                        tbody.html('<tr><td colspan="7" class="text-center text-muted">Data bahan baku tidak ditemukan</td></tr>');
                        return;
                    }

                    data.forEach(function (detail) {
                        let hargaDapur = detail.harga_dapur || 0;
                        let hargaMitra = detail.harga_mitra || 0;
                        let subtotalDapur = hargaDapur * detail.qty_digunakan;
                        let subtotalMitra = hargaMitra * detail.qty_digunakan;

                        tbody.append(`
                            <tr>
                                <td>${detail.bahan_baku_nama || '-'}</td>
                                <td>${parseFloat(detail.qty_digunakan).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${detail.satuan || '-'}</td>
                                <td>${formatRupiah(hargaDapur)}</td>
                                <td>${formatRupiah(hargaMitra)}</td>
                                <td>${formatRupiah(subtotalDapur)}</td>
                                <td>${formatRupiah(subtotalMitra)}</td>
                            </tr>
                        `);
                    });
                })
                .fail(function () {
                    tbody.html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data bahan baku</td></tr>');
                });
        }

        /**
         * ======================================================
         * AUTO FILL SATUAN SAAT PILIH BAHAN BAKU
         * ======================================================
         */
        $(document).on('change', '.bahan-tambah-select', function() {
            let row = $(this).closest('.bahan-tambah-group');
            let bahanId = $(this).val();
            
            if (!bahanId) {
                row.find('.satuan-tambah-text').val('');
                return;
            }

            // Ambil satuan dari data bahan baku yang sudah di-load menggunakan kitchenId yang sudah tersimpan
            let url = "{{ route('transaction.submission.bahan-baku-by-kitchen', ':kitchen') }}";
            let kitchenId = currentKitchenId;
            if (!kitchenId) {
                row.find('.satuan-tambah-text').val('-');
                return;
            }
            url = url.replace(':kitchen', kitchenId);

            $.get(url)
                .done(function(data) {
                    let selectedBahan = data.find(b => b.id == bahanId);
                    if (selectedBahan && selectedBahan.satuan) {
                        row.find('.satuan-tambah-text').val(selectedBahan.satuan);
                    } else {
                        row.find('.satuan-tambah-text').val('-');
                    }
                });
        });


        /**
         * ======================================================
         * RESET FORM TAMBAH INLINE
         * ======================================================
         */
        function resetFormTambahInline() {
            let wrapper = $('#tambah-bahan-wrapper');
            let firstRow = wrapper.find('.bahan-tambah-group').first();
            
            if (firstRow.length === 0) return;
            
            // Reset semua input di baris pertama
            firstRow.find('input').val('');
            firstRow.find('select').val('');
            firstRow.find('.satuan-tambah-text').val('');
            firstRow.find('.remove-bahan-tambah').addClass('d-none');
            
            // Hapus semua baris kecuali yang pertama
            wrapper.find('.bahan-tambah-group').not(':first').remove();
        }

    </script>



@endpush