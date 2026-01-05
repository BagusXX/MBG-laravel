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
                                        @else
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
                                                <form 
                                                    action="{{ route('transaction.submission.to-complete', $item->id) }}" 
                                                    method="POST" 
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin mengubah status menjadi selesai?')"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        Selesai
                                                    </button>
                                                </form>
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


    {{-- MODAL EDIT DETAIL (PERMINTAAN) --}}
    @if($mode === 'permintaan')
        <x-modal-form id="modalEditDetail" size="modal-lg" title="Edit Detail Permintaan" action="" submitText="Update">
            @method('PUT')

            <div class="form-group">
                <label>Kode</label>
                <input type="text" id="edit_kode" class="form-control" readonly style="background:#e9ecef">
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" id="edit_tanggal" name="tanggal" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Dapur</label>
                <select name="kitchen_id" id="edit_kitchen_id" class="form-control" required>
                    <option disabled selected>Pilih Dapur</option>
                    @foreach($kitchens as $kitchen)
                        <option value="{{ $kitchen->id }}">{{ $kitchen->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Menu</label>
                <select name="menu_id" id="edit_menu_id" class="form-control" required>
                    <option disabled selected>Pilih dapur terlebih dahulu</option>
                </select>
            </div>

            <div class="form-group">
                <label>Porsi</label>
                <input type="number" name="porsi" id="edit_porsi" min="1" class="form-control" required>
            </div>

            <hr>
            <h6 class="font-weight-bold">Detail Bahan Baku</h6>
            <div id="edit_bahan_baku_list" class="table-responsive">
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
                    <tbody id="edit_bahan_tbody">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Pilih menu untuk melihat detail bahan baku</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button type="button" id="btn-edit-harga" class="btn btn-warning btn-sm" style="display: none;">
                    <i class="fas fa-edit"></i> Edit Harga
                </button>
                <button type="button" id="btn-save-harga" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-save"></i> Simpan Harga
                </button>
                <button type="button" id="btn-cancel-edit-harga" class="btn btn-secondary btn-sm" style="display: none;">
                    <i class="fas fa-times"></i> Batal
                </button>
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
                </table>
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
                    <tbody>
                        @forelse ($item->details as $detail)
                            @php
                                $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
                                $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
                                $subtotalDapur = $hargaDapur * $detail->qty_digunakan;
                                $subtotalMitra = $hargaMitra * $detail->qty_digunakan;
                            @endphp
                            <tr>
                                <td>{{ $detail->recipe?->bahan_baku?->nama ?? '-' }}</td>
                                <td>{{ number_format($detail->qty_digunakan, 2, ',', '.') }}</td>
                                <td>{{ $detail->recipe?->bahan_baku?->unit?->satuan ?? '-' }}</td>
                                <td>Rp {{ number_format($hargaDapur, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($hargaMitra, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($subtotalDapur, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($subtotalMitra, 0, ',', '.') }}</td>
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
                    if (selectedMenuId) {
                        loadBahanBakuByMenu(selectedMenuId);
                    }
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
        function loadSubmissionDetails(submissionId) {
            let tbody = $('#edit_bahan_tbody');
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
                        tbody.append(`
                            <tr data-detail-id="${detail.id}">
                                <td>${detail.bahan_baku_nama}</td>
                                <td>${parseFloat(detail.qty_digunakan).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${detail.satuan}</td>
                                <td class="harga-dapur-cell">
                                    <span class="harga-dapur-display">${formatRupiah(detail.harga_dapur)}</span>
                                    <input type="number" class="form-control form-control-sm harga-dapur-input" value="${detail.harga_dapur}" min="0" step="0.01" style="display: none;">
                                </td>
                                <td class="harga-mitra-cell">
                                    <span class="harga-mitra-display">${formatRupiah(detail.harga_mitra)}</span>
                                    <input type="number" class="form-control form-control-sm harga-mitra-input" value="${detail.harga_mitra}" min="0" step="0.01" style="display: none;">
                                </td>
                                <td class="subtotal-dapur-cell">${formatRupiah(detail.subtotal_dapur)}</td>
                                <td class="subtotal-mitra-cell">${formatRupiah(detail.subtotal_mitra)}</td>
                            </tr>
                        `);
                    });
                    
                    // Tampilkan tombol edit harga jika ada data
                    if (data.length > 0) {
                        $('#btn-edit-harga').show();
                    }
                })
                .fail(function () {
                    tbody.html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data bahan baku</td></tr>');
                });
        }

        /**
         * ======================================================
         * LOAD BAHAN BAKU BERDASARKAN MENU (DARI RECIPE)
         * ======================================================
         */
        function loadBahanBakuByMenu(menuId, kitchenId, porsi = 1) {
            let tbody = $('#edit_bahan_tbody');
            tbody.html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

            // Load dari recipe
            let url = "{{ route('recipe.detail', [':menu', ':kitchen']) }}";
            url = url.replace(':menu', menuId).replace(':kitchen', kitchenId);

            $.get(url)
                .done(function (data) {
                    tbody.empty();

                    if (data.length === 0) {
                        tbody.html('<tr><td colspan="7" class="text-center text-muted">Tidak ada bahan baku untuk menu ini</td></tr>');
                        return;
                    }

                    data.forEach(function (recipe) {
                        let qty = (recipe.jumlah || 0) * porsi;
                        let hargaSatuan = recipe.bahan_baku?.harga || 0;
                        let hargaDapur = hargaSatuan;
                        let hargaMitra = hargaSatuan; // Bisa dikembangkan dengan perhitungan markup nanti
                        let subtotalDapur = hargaDapur * qty;
                        let subtotalMitra = hargaMitra * qty;

                        tbody.append(`
                            <tr>
                                <td>${recipe.bahan_baku?.nama || '-'}</td>
                                <td>${parseFloat(qty).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${recipe.bahan_baku?.unit?.satuan || '-'}</td>
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

        // Simpan submissionId global untuk digunakan di fungsi lain
        let currentSubmissionId = null;

        /**
         * ======================================================
         * SAAT TOMBOL DETAIL DIKLIK (MODE PERMINTAAN)
         * ======================================================
         */
        $(document).on('click', '.btnEditDetail', function () {
            let submissionId = $(this).data('id');
            currentSubmissionId = submissionId; // Simpan untuk digunakan di fungsi lain
            
            let kode = $(this).data('kode');
            let tanggal = $(this).data('tanggal');
            let kitchenId = $(this).data('kitchen-id');
            let menuId = $(this).data('menu-id');
            let porsi = $(this).data('porsi');
            let action = $(this).data('action');

            // Set form action
            $('#modalEditDetail form').attr('action', action);

            // Set form values
            $('#edit_kode').val(kode);
            $('#edit_tanggal').val(tanggal);
            $('#edit_kitchen_id').val(kitchenId);
            $('#edit_porsi').val(porsi);

            // Load menu berdasarkan dapur
            loadMenuByKitchenForEdit(kitchenId, menuId);

            // Load detail bahan baku dari submission yang sudah ada
            loadSubmissionDetails(submissionId);
        });

        /**
         * ======================================================
         * SAAT DAPUR DIUBAH DI MODAL EDIT
         * ======================================================
         */
        $(document).on('change', '#edit_kitchen_id', function () {
            let kitchenId = $(this).val();
            if (kitchenId) {
                loadMenuByKitchenForEdit(kitchenId);
            } else {
                $('#edit_menu_id').html('<option disabled selected>Pilih dapur terlebih dahulu</option>');
            }
        });

        /**
         * ======================================================
         * SAAT MENU DIUBAH DI MODAL EDIT
         * ======================================================
         */
        $(document).on('change', '#edit_menu_id', function () {
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
        $(document).on('change', '#edit_porsi', function () {
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
         * TOMBOL EDIT HARGA
         * ======================================================
         */
        $(document).on('click', '#btn-edit-harga', function () {
            // Tampilkan input, sembunyikan display
            $('.harga-dapur-display, .harga-mitra-display').hide();
            $('.harga-dapur-input, .harga-mitra-input').show();
            
            // Sembunyikan tombol edit, tampilkan tombol save dan cancel
            $('#btn-edit-harga').hide();
            $('#btn-save-harga, #btn-cancel-edit-harga').show();
        });

        /**
         * ======================================================
         * TOMBOL CANCEL EDIT HARGA
         * ======================================================
         */
        $(document).on('click', '#btn-cancel-edit-harga', function () {
            // Reload data untuk reset
            if (currentSubmissionId) {
                loadSubmissionDetails(currentSubmissionId);
            }
            
            // Sembunyikan tombol save dan cancel, tampilkan tombol edit
            $('#btn-save-harga, #btn-cancel-edit-harga').hide();
            $('#btn-edit-harga').show();
        });

        /**
         * ======================================================
         * UPDATE SUBTOTAL SAAT HARGA DIUBAH
         * ======================================================
         */
        $(document).on('input', '.harga-dapur-input, .harga-mitra-input', function () {
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('td').eq(1).text().replace(/\./g, '').replace(',', '.')) || 0;
            let hargaDapur = parseFloat(row.find('.harga-dapur-input').val()) || 0;
            let hargaMitra = parseFloat(row.find('.harga-mitra-input').val()) || 0;
            
            let subtotalDapur = hargaDapur * qty;
            let subtotalMitra = hargaMitra * qty;
            
            row.find('.subtotal-dapur-cell').text(formatRupiah(subtotalDapur));
            row.find('.subtotal-mitra-cell').text(formatRupiah(subtotalMitra));
        });

        /**
         * ======================================================
         * TOMBOL SAVE HARGA
         * ======================================================
         */
        $(document).on('click', '#btn-save-harga', function () {
            if (!currentSubmissionId) {
                alert('Submission ID tidak ditemukan');
                return;
            }

            // Kumpulkan data harga
            let details = [];
            $('#edit_bahan_tbody tr[data-detail-id]').each(function () {
                let detailId = $(this).data('detail-id');
                let hargaDapur = parseFloat($(this).find('.harga-dapur-input').val()) || 0;
                let hargaMitra = parseFloat($(this).find('.harga-mitra-input').val()) || 0;
                
                details.push({
                    id: detailId,
                    harga_dapur: hargaDapur,
                    harga_mitra: hargaMitra
                });
            });

            // Kirim ke server
            let url = "{{ route('transaction.submission.update-harga', ':id') }}";
            url = url.replace(':id', currentSubmissionId);

            $.ajax({
                url: url,
                method: 'PATCH',
                data: {
                    details: details,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    // Reload data
                    loadSubmissionDetails(currentSubmissionId);
                    
                    // Sembunyikan tombol save dan cancel, tampilkan tombol edit
                    $('#btn-save-harga, #btn-cancel-edit-harga').hide();
                    $('#btn-edit-harga').show();
                    
                    // Tampilkan notifikasi sukses
                    alert('Harga berhasil diperbarui');
                },
                error: function (xhr) {
                    let message = xhr.responseJSON?.message || 'Gagal memperbarui harga';
                    alert(message);
                }
            });
        });

    </script>



@endpush