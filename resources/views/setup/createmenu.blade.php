@extends('adminlte::page')

@section('title', 'Racik Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
    <style>
        /* Sedikit style tambahan agar input group rapi */
        .bahan-group {
            border-bottom: 1px dashed #ddd;
            padding-bottom: 10px;
        }

        .bahan-group:last-child {
            border-bottom: none;
        }
    </style>
@endsection

@section('content_header')
    <h1>Racik Menu</h1>
@endsection

@section('content')
    <x-button-add idTarget="#modalAddRecipe" text="Racik Menu" />

    <x-notification-pop-up />

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%">No</th>
                        <th>Dapur</th>
                        <th>Nama Menu</th>
                        {{-- Kolom Harga Dihapus --}}
                        <th style="width: 20%">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php $no = 1; @endphp
                    {{-- Loop Menus dari Controller --}}
                    @forelse ($menus as $menu)
                        {{-- Grouping Resep berdasarkan Kitchen ID agar tampil per baris (Unik: Menu + Kitchen) --}}
                        @php
                            $recipesByKitchen = $menu->recipes->groupBy('kitchen_id');
                        @endphp

                        @foreach ($recipesByKitchen as $kitchenId => $ingredients)
                            @php
                                $kitchen = $ingredients->first()->kitchen;
                                // Perhitungan total harga dihapus
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $kitchen->nama }}</td>
                                <td>{{ $menu->nama }}</td>
                                {{-- Cell Harga Dihapus --}}
                                <td>
                                    {{-- Tombol Detail (Via AJAX) --}}
                                    <button type="button" class="btn btn-primary btn-sm btnDetailRecipe"
                                        data-menu="{{ $menu->id }}" data-kitchen="{{ $kitchenId }}"
                                        data-toggle="modal" data-target="#modalDetailRecipe">
                                        Detail
                                    </button>

                                    {{-- Tombol Edit --}}
                                    <button type="button" class="btn btn-warning btn-sm btnEditRecipe"
                                        data-menu="{{ $menu->id }}" data-kitchen="{{ $kitchenId }}"
                                        data-toggle="modal" data-target="#modalEditRecipe">
                                        Edit
                                    </button>

                                    {{-- Tombol Delete --}}
                                    <x-button-delete idTarget="#modalDeleteRecipe" formId="formDeleteRecipe"
                                        action="{{ route('recipe.destroy', ['menu' => $menu->id, 'kitchen' => $kitchenId]) }}"
                                        text="Hapus" />
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada racikan menu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD RECIPE --}}
    <x-modal-form id="modalAddRecipe" size="modal-lg" title="Racik Menu Baru" action="{{ route('recipe.store') }}"
        submitText="Simpan">
        <div class="form-group">
            <label>Nama Dapur</label>
            <select class="form-control kitchen-select" name="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach ($kitchens as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <select class="form-control menu-select" name="menu_id" required>
                <option value="" disabled selected>Pilih Menu</option>
                {{-- Opsi menu akan dimuat via JS --}}
            </select>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Komposisi Bahan</label>
            {{-- Header Kolom Bahan (Harga dihapus, kolom diperlebar) --}}
            <div class="form-row mb-2 small text-muted font-weight-bold">
                <div class="col-md-6">Bahan Baku</div>
                <div class="col-md-3">Jumlah</div>
                <div class="col-md-2">Satuan</div>
                {{-- Kolom Harga Dihapus --}}
                <div class="col-md-1"></div>
            </div>

            <div id="bahan-wrapper-add">
                {{-- Template Row Pertama --}}
                <div class="form-row mb-3 bahan-group">
                    <div class="col-md-6">
                        <select name="bahan_baku_id[]" class="form-control bahan-select" required>
                            <option value="" disabled selected>Pilih Bahan</option>
                            @foreach ($bahanBaku as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="number" step="any" name="jumlah[]" class="form-control" placeholder="0" required>
                    </div>

                    <div class="col-md-2">
                        <input type="text" class="form-control satuan-text bg-light" placeholder="-" readonly>
                        <input type="hidden" name="satuan_id[]" class="satuan-id">
                    </div>

                    {{-- Input Harga Dihapus --}}

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none w-100">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-bahan-add" class="btn btn-outline-primary btn-sm mt-2">
                <i class="fas fa-plus mr-1"></i> Tambah Bahan Lain
            </button>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT RECIPE --}}
    <x-modal-form id="modalEditRecipe" size="modal-lg" title="Edit Racikan Menu" action="" submitText="Perbarui">
        @method('PUT')

        {{-- Input Hidden untuk Identifikasi --}}
        <input type="hidden" name="kitchen_id" id="edit_kitchen_id">
        <input type="hidden" name="menu_id" id="edit_menu_id">

        <div class="form-group">
            <label>Dapur</label>
            <input type="text" class="form-control" id="display_kitchen_name" readonly disabled>
        </div>

        <div class="form-group">
            <label>Menu</label>
            <input type="text" class="form-control" id="display_menu_name" readonly disabled>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Komposisi Bahan</label>
            <div class="form-row mb-2 small text-muted font-weight-bold">
                <div class="col-md-6">Bahan Baku</div>
                <div class="col-md-3">Jumlah</div>
                <div class="col-md-2">Satuan</div>
                {{-- Kolom Harga Dihapus --}}
                <div class="col-md-1"></div>
            </div>

            <div id="bahan-wrapper-edit">
                {{-- Rows akan digenerate via JS --}}
            </div>

            <button type="button" id="add-bahan-edit" class="btn btn-outline-primary btn-sm mt-2">
                <i class="fas fa-plus mr-1"></i> Tambah Bahan Lain
            </button>
        </div>
    </x-modal-form>

    {{-- MODAL DETAIL (SINGLE DYNAMIC MODAL) --}}
    <x-modal-detail id="modalDetailRecipe" size="modal-lg" title="Detail Racikan Menu">
        <div id="detailContent" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </x-modal-detail>

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeleteRecipe" formId="formDeleteRecipe" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus seluruh racikan untuk menu di dapur ini?" confirmText="Hapus" />
@endsection

@push('js')
    <script>
        // Simpan data master bahan baku ke global variable agar ringan
        window.BAHAN_LIST = @json($bahanBaku);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- FUNGSI HELPER: Dynamic Form Rows ---
            function initDynamicForm(wrapperId, addBtnId) {
                const wrapper = document.getElementById(wrapperId);
                const addBtn = document.getElementById(addBtnId);

                if (!wrapper || !addBtn) return;

                addBtn.addEventListener('click', function() {
                    // Clone row pertama atau buat baru jika kosong (untuk edit)
                    let templateRow = wrapper.querySelector('.bahan-group');

                    // Jika wrapper kosong (kasus edit awal kosong), kita harus buat string HTML manual
                    let newRow;
                    if (templateRow) {
                        newRow = templateRow.cloneNode(true);
                        // Reset values
                        newRow.querySelectorAll('input').forEach(inp => inp.value = '');
                        newRow.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
                        // Hapus hidden ID (row_id) jika ada (agar dianggap data baru)
                        const hiddenId = newRow.querySelector('input[name="row_id[]"]');
                        if (hiddenId) hiddenId.remove();
                    } else {
                        return;
                    }

                    // Tampilkan tombol hapus
                    const removeBtn = newRow.querySelector('.remove-bahan');
                    removeBtn.classList.remove('d-none');
                    removeBtn.addEventListener('click', () => newRow.remove());

                    wrapper.appendChild(newRow);
                });
            }

            // Init Dynamic Form untuk Add dan Edit
            initDynamicForm('bahan-wrapper-add', 'add-bahan-add');

            // --- GLOBAL EVENT: Hapus Baris Bahan ---
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-bahan')) {
                    e.target.closest('.bahan-group').remove();
                }
            });

            // --- GLOBAL EVENT: Auto Fill Satuan saat Pilih Bahan (Harga Dihapus) ---
            document.addEventListener('change', function(e) {
                if (!e.target.matches('select[name="bahan_baku_id[]"]')) return;

                const row = e.target.closest('.bahan-group');
                const bahanId = e.target.value;

                // Cari data di window.BAHAN_LIST
                const selectedBahan = window.BAHAN_LIST.find(b => b.id == bahanId);

                if (selectedBahan) {
                    const satuanText = selectedBahan.unit ? selectedBahan.unit.satuan : '-';
                    row.querySelector('.satuan-text').value = satuanText;
                    if (row.querySelector('.satuan-id')) row.querySelector('.satuan-id').value =
                        selectedBahan.satuan_id;

                    // Logic pengisian harga dihapus
                }
            });

            // --- LOGIC: Fetch Menu berdasarkan Kitchen (Modal Add) ---
            document.querySelectorAll('.kitchen-select').forEach(kitchenSelect => {
                kitchenSelect.addEventListener('change', function() {
                    const kitchenId = this.value;
                    const form = this.closest('form');
                    const menuSelect = form.querySelector('.menu-select');

                    if (!menuSelect) return;

                    menuSelect.innerHTML = '<option disabled selected>Loading...</option>';

                    fetch(`/dashboard/setup/racik-menu/menus-by-kitchen/${kitchenId}`)
                        .then(res => res.json())
                        .then(data => {
                            menuSelect.innerHTML =
                                '<option disabled selected>Pilih Menu</option>';
                            data.forEach(menu => {
                                const option = document.createElement('option');
                                option.value = menu.id;
                                option.textContent = menu.nama;
                                menuSelect.appendChild(option);
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            menuSelect.innerHTML =
                                '<option disabled>Gagal memuat menu</option>';
                        });
                });
            });

            // --- LOGIC: Tombol EDIT ---
            document.querySelectorAll('.btnEditRecipe').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menu;
                    const kitchenId = this.dataset.kitchen;

                    // Ambil nama menu/dapur dari baris tabel
                    const row = this.closest('tr');
                    const kitchenName = row.children[1].textContent;
                    const menuName = row.children[2].textContent;

                    const modal = document.getElementById('modalEditRecipe');
                    const form = modal.querySelector('form'); <<
                    <<
                    << < HEAD

                    // set kitchen
                    form.querySelector('.kitchen-select').value = kitchenId; ===
                    ===
                    =
                    const wrapper = document.getElementById('bahan-wrapper-edit');

                    // Set Action URL
                    form.action = `/dashboard/setup/racik-menu/${menuId}/${kitchenId}`;

                    // Set Hidden & Display Values
                    document.getElementById('edit_kitchen_id').value = kitchenId;
                    document.getElementById('edit_menu_id').value = menuId;
                    document.getElementById('display_kitchen_name').value = kitchenName;
                    document.getElementById('display_menu_name').value = menuName; >>>
                    >>> > 1 f29db55143ce14dda1435baae18ea1a0dbff469

                    // Loading State
                    wrapper.innerHTML =
                        '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat bahan...</div>';

                    // Fetch Data Detail
                    fetch(`/dashboard/setup/racik-menu/detail/${menuId}/${kitchenId}`)
                        .then(res => res.json())
                        .then(items => {
                            wrapper.innerHTML = ''; // Clear loading

                            if (items.length === 0) {
                                wrapper.innerHTML =
                                    '<p class="text-muted">Data tidak ditemukan</p>';
                                return;
                            }

                            items.forEach(item => {
                                const bahanHtml = generateBahanRowHtml(item);
                                wrapper.insertAdjacentHTML('beforeend', bahanHtml);
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            wrapper.innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
                        });
                });
            });

            // Helper: Generate HTML Row untuk Edit (TANPA HARGA)
            function generateBahanRowHtml(item = null) {
                let options = '<option value="" disabled>Pilih Bahan</option>';
                const currentBahanId = item ? item.bahan_baku_id : '';

                window.BAHAN_LIST.forEach(b => {
                    const selected = b.id == currentBahanId ? 'selected' : '';
                    options += `<option value="${b.id}" ${selected}>${b.nama}</option>`;
                });

                const rowIdInput = item ? `<input type="hidden" name="row_id[]" value="${item.id}">` : '';
                const jumlahVal = item ? item.jumlah : '';
                const satuanVal = item && item.bahan_baku && item.bahan_baku.unit ? item.bahan_baku.unit.satuan :
                    '-';

                // Layout kolom disesuaikan (Total 12 grid)
                return `
                        <div class="form-row mb-3 bahan-group">
                            ${rowIdInput}
                            <div class="col-md-6">
                                <select name="bahan_baku_id[]" class="form-control" required>
                                    ${options}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="any" name="jumlah[]" class="form-control" value="${jumlahVal}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control satuan-text bg-light" value="${satuanVal}" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-bahan w-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `;
            }

            // Logic Tambah Baris di Modal Edit
            const btnAddEdit = document.getElementById('add-bahan-edit');
            if (btnAddEdit) {
                btnAddEdit.addEventListener('click', function() {
                    const wrapper = document.getElementById('bahan-wrapper-edit');
                    // Generate baris kosong
                    const emptyRow = generateBahanRowHtml(null);
                    wrapper.insertAdjacentHTML('beforeend', emptyRow);
                });
            }

            // --- LOGIC: Tombol Detail (AJAX - TANPA HARGA) ---
            document.querySelectorAll('.btnDetailRecipe').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menu;
                    const kitchenId = this.dataset.kitchen;
                    const container = document.getElementById('detailContent');

                    container.innerHTML =
                        '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

                    fetch(`/dashboard/setup/racik-menu/detail/${menuId}/${kitchenId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data || data.length === 0) {
                                container.innerHTML =
                                    '<p class="text-center">Data tidak ditemukan.</p>';
                                return;
                            }

                            // Ambil info header dari item pertama
                            const kitchenName = data[0].kitchen ? data[0].kitchen.nama : '-';

                            let rows = '';

                            data.forEach(item => {
                                const satuan = item.bahan_baku && item.bahan_baku.unit ?
                                    item.bahan_baku.unit.satuan : '';

                                rows += `
                                            <tr>
                                                <td>${item.bahan_baku ? item.bahan_baku.nama : '-'}</td>
                                                <td>${item.jumlah} ${satuan}</td>
                                            </tr>
                                        `;
                            });

                            // Tabel Detail tanpa kolom harga dan tanpa footer total
                            const html = `
                                        <div class="row mb-3 text-left">
                                            <div class="col-md-6"><strong>Dapur:</strong> ${kitchenName}</div>
                                            <div class="col-md-6"><strong>Menu ID:</strong> ${menuId}</div>
                                        </div>
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Bahan Baku</th>
                                                    <th>Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>${rows}</tbody>
                                        </table>
                                    `;
                            container.innerHTML = html;
                        })
                        .catch(err => {
                            console.error(err);
                            container.innerHTML =
                                '<p class="text-danger text-center">Terjadi kesalahan saat memuat data.</p>';
                        });
                });
            });

            // --- LOGIC: Delete ---
            $('#modalDeleteRecipe').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var modal = $(this);
                modal.find('#formDeleteRecipe').attr('action', action);
            });

        });
    </script>
@endpush
