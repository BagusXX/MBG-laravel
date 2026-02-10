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
                        <th style="width: 25%">Aksi</th>
                    </tr>
                </thead>
`
                <tbody>
                    @php 
                        $no = ($menus->currentPage() - 1) * $menus->perPage() + 1; 
                    @endphp
                    {{-- Loop Menus dari Controller --}}
                    @forelse ($menus as $menu)
                        {{-- Grouping Resep berdasarkan Kitchen ID agar tampil per baris (Unik: Menu + Kitchen) --}}
                        @php
                            $recipesByKitchen = $menu->recipes->groupBy('kitchen_id');
                        @endphp

                        @foreach ($recipesByKitchen as $kitchenId => $ingredients)
                            @php
                                $kitchen = $ingredients->first()->kitchen;
                                $isUsedInSubmission = $ingredients->sum('submission_details_count') > 0;
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
                                        data-menu="{{ $menu->id }}" 
                                        data-kitchen="{{ $kitchenId }}"
                                        data-is-used="{{ $isUsedInSubmission ? 'true' : 'false' }}" {{-- Tambahkan data attribute --}}
                                    >
                                        @if($isUsedInSubmission)
                                            <i class="fas fa-lock mr-1"></i>
                                            Edit
                                        @else
                                            Edit
                                        @endif
                                    </button>

                                    @if($isUsedInSubmission)
                                        <button 
                                            type="button" 
                                            class="btn btn-danger btn-sm btnLockedDelete"
                                            data-message="Resep tidak bisa dihapus karena sudah digunakan dalam data Pengajuan."
                                        >
                                            <i class="fas fa-lock mr-1"></i> Hapus
                                        </button>
                                    @else
                                        <x-button-delete idTarget="#modalDeleteRecipe" formId="formDeleteRecipe"
                                            action="{{ route('recipe.destroy', ['menu' => $menu->id, 'kitchen' => $kitchenId]) }}"
                                            text="Hapus" />
                                    @endif
                                    
                                    <button type="button" class="btn btn-info btn-sm btnDuplicateRecipe"
                                        data-menu="{{ $menu->id }}" 
                                        data-kitchen="{{ $kitchenId }}"
                                        data-menuname="{{ $menu->nama }}" 
                                        data-toggle="modal" 
                                        data-target="#modalDuplicateRecipe">
                                        <i class="fas fa-copy mr-1"></i> Copy
                                    </button>
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
            <div class="mt-3 d-flex justify-content-end">
                {{-- Gunakan pagination::bootstrap-4 jika menggunakan AdminLTE --}}
                {{ $menus->links('pagination::bootstrap-4') }}
            </div>
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
    
    {{-- MODAL DUPLICATE--}}
    <x-modal-form id="modalDuplicateRecipe" title="Duplikasi Menu" action="{{ route('recipe.duplicate') }}"
        submitText="Duplikasi Menu">
        
        {{-- Input Hidden untuk ID --}}
        <input type="hidden" name="original_menu_id" id="dup_original_menu_id">
        <input type="hidden" name="kitchen_id" id="dup_kitchen_id">

        <div class="form-group">
            <label>Menu Asumber</label>
            <input type="text" class="form-control" id="dup_display_menu_name" readonly disabled>
            <small class="text-muted">Resep dari menu ini akan disalin.</small>
        </div>

        <div class="form-group">
            <label>Nama Menu Baru <span class="text-danger">*</span></label>
            <input type="text" name="new_menu_name" class="form-control" placeholder="Contoh: Nasi Goreng Spesial (Copy)" required>
            <small class="text-muted">Harga menu baru akan diset ke 0.</small>
        </div>
    </x-modal-form>

    {{-- MODAL DETAIL (SINGLE DYNAMIC MODAL) --}}
    <x-modal-detail id="modalDetailRecipe" size="modal-lg" title="Detail Racikan Menu">
        <div id="detailContent">
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
    @include('components.notification-pop-up-script')
    <script>
        // Simpan data master bahan baku ke global variable agar ringan
        window.BAHAN_LIST = @json($bahanBaku);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            $(document).on('click', '.btnDuplicateRecipe', function() {
                // Ambil data dari tombol
                let menuId = $(this).data('menu');
                let kitchenId = $(this).data('kitchen');
                let menuName = $(this).data('menuname');

                // Isi ke dalam input modal
                $('#dup_original_menu_id').val(menuId);
                $('#dup_kitchen_id').val(kitchenId);
                $('#dup_display_menu_name').val(menuName);
                
                // Reset input nama baru agar kosong saat dibuka
                $('#modalDuplicateRecipe').find('input[name="new_menu_name"]').val(menuName + ' (Copy)');
            });


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
                        newRow.querySelectorAll('input').forEach(inp => {
                            if (inp.type !== 'hidden') inp.value = '';
                        });
                        
                        // Update select bahan baku dengan bahan dari dapur yang dipilih
                        const bahanSelect = newRow.querySelector('select[name="bahan_baku_id[]"]');
                        if (bahanSelect && window.BAHAN_LIST) {
                            bahanSelect.innerHTML = '<option value="" disabled selected>Pilih Bahan</option>';
                            window.BAHAN_LIST.forEach(bahan => {
                                const option = document.createElement('option');
                                option.value = bahan.id;
                                option.textContent = bahan.nama;
                                bahanSelect.appendChild(option);
                            });
                        } else {
                            bahanSelect.selectedIndex = 0;
                        }
                        
                        // Reset satuan
                        const satuanText = newRow.querySelector('.satuan-text');
                        const satuanId = newRow.querySelector('.satuan-id');
                        if (satuanText) satuanText.value = '';
                        if (satuanId) satuanId.value = '';
                        
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

            let hasShownDeleteWarning = false;

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btnLockedDelete');
                if (!btn) return;

                if (hasShownDeleteWarning) return;

                hasShownDeleteWarning = true;

                showNotificationPopUp(
                    'warning',
                    btn.dataset.message,
                    'Aksi Ditolak'
                );

                // Reset setelah popup selesai (samakan dengan durasi toast)
                setTimeout(() => {
                    hasShownDeleteWarning = false;
                }, 7000);
            });

            // --- GLOBAL EVENT: Auto Fill Satuan saat Pilih Bahan (Harga Dihapus) ---
            document.addEventListener('change', function(e) {
                if (!e.target.matches('select[name="bahan_baku_id[]"]')) return;

                // const row = e.target.closest('.bahan-group');
                // const bahanId = e.target.value;

                // // Cari data di window.BAHAN_LIST
                // const selectedBahan = window.BAHAN_LIST.find(b => b.id == bahanId);

                // if (selectedBahan) {
                //     const satuanText = selectedBahan.unit ? selectedBahan.unit.satuan : '-';
                //     row.querySelector('.satuan-text').value = satuanText;
                //     if (row.querySelector('.satuan-id')) row.querySelector('.satuan-id').value =
                //         selectedBahan.satuan_id;
                // }
            });

            // --- LOGIC: Fetch Menu dan Bahan Baku berdasarkan Kitchen (Modal Add) ---
            document.querySelectorAll('.kitchen-select').forEach(kitchenSelect => {
                kitchenSelect.addEventListener('change', function() {
                    const kitchenId = this.value;
                    const form = this.closest('form');
                    const menuSelect = form.querySelector('.menu-select');
                    const bahanWrapper = form.querySelector('#bahan-wrapper-add');

                    if (!kitchenId) {
                        // Reset jika dapur tidak dipilih
                        if (menuSelect) {
                            menuSelect.innerHTML = '<option value="" disabled selected>Pilih Menu</option>';
                        }
                        return;
                    }

                    // Reset dan load menu
                    if (menuSelect) {
                        menuSelect.innerHTML = '<option disabled selected>Loading...</option>';
                        menuSelect.disabled = true;
                        menuSelect.removeAttribute('readonly');

                        fetch(`/dashboard/setup/racik-menu/menus-by-kitchen/${kitchenId}`)
                            .then(res => {
                                if (!res.ok) {
                                    throw new Error(`HTTP error! status: ${res.status}`);
                                }
                                return res.json();
                            })
                            .then(data => {
                                menuSelect.innerHTML = '<option value="" disabled selected>Pilih Menu</option>';
                                if (!data || data.length === 0) {
                                    menuSelect.innerHTML += '<option disabled>Tidak ada menu untuk dapur ini</option>';
                                } else {
                                    data.forEach(menu => {
                                        const option = document.createElement('option');
                                        option.value = menu.id;
                                        option.textContent = menu.nama;
                                        menuSelect.appendChild(option);
                                    });
                                }
                                menuSelect.disabled = false;
                                menuSelect.removeAttribute('readonly');
                            })
                            .catch(err => {
                                console.error('Error loading menu:', err);
                                menuSelect.innerHTML = '<option value="" disabled selected>Gagal memuat menu</option>';
                                menuSelect.disabled = false;
                                menuSelect.removeAttribute('readonly');
                            });
                    }

                    // Reset dan load bahan baku
                    if (bahanWrapper) {
                        const bahanSelects = bahanWrapper.querySelectorAll('select[name="bahan_baku_id[]"]');
                        
                        // Load bahan baku dari server
                        fetch(`/dashboard/setup/racik-menu/bahan-by-kitchen/${kitchenId}`)
                            .then(res => res.json())
                            .then(bahanList => {
                                // Update semua select bahan baku
                                bahanSelects.forEach(select => {
                                    const currentValue = select.value;
                                    select.innerHTML = '<option value="" disabled selected>Pilih Bahan</option>';
                                    
                                    if (bahanList.length === 0) {
                                        select.innerHTML += '<option disabled>Tidak ada bahan baku untuk dapur ini</option>';
                                    } else {
                                        bahanList.forEach(bahan => {
                                            const option = document.createElement('option');
                                            option.value = bahan.id;
                                            option.textContent = bahan.nama;
                                            // Restore previous selection if still valid
                                            if (currentValue == bahan.id) {
                                                option.selected = true;
                                                // Trigger change untuk update satuan
                                                select.dispatchEvent(new Event('change'));
                                            }
                                            select.appendChild(option);
                                        });
                                    }
                                });

                                // Update window.BAHAN_LIST untuk dapur ini
                                window.BAHAN_LIST = bahanList.map(b => ({
                                    id: b.id,
                                    nama: b.nama,
                                    harga: b.harga,
                                    satuan_id: b.satuan_id,
                                    unit: b.satuan ? { satuan: b.satuan } : null
                                }));
                            })
                            .catch(err => {
                                console.error(err);
                                bahanSelects.forEach(select => {
                                    select.innerHTML = '<option disabled>Gagal memuat bahan baku</option>';
                                });
                            });
                    }
                });
            });

            // --- LOGIC: Tombol EDIT ---
            let hasShownEditWarning = false;

            document.querySelectorAll('.btnEditRecipe').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menu;
                    const kitchenId = this.dataset.kitchen;
                    const isUsed = this.dataset.isUsed === 'true';
                    
                    const modal = document.getElementById('modalEditRecipe');
                    const form = modal.querySelector('form');
                    const wrapper = document.getElementById('bahan-wrapper-edit');
                    const btnSubmit = modal.querySelector('button[type="submit"]');
                    const btnAddBahan = document.getElementById('add-bahan-edit');

                    if (isUsed) {
                        if (hasShownEditWarning) return;

                        hasShownEditWarning = true;
                        
                        showNotificationPopUp(
                            'warning',
                            'Resep ini sudah digunakan dalam data Pengajuan. Anda tidak dapat mengubah komposisi bahan untuk menjaga validitas data laporan.',
                            'Perhatian'
                        );
                        
                        setTimeout(() => {
                            hasShownEditWarning = false;
                        }, 7000);
                        return;
                    }

                    $('#modalEditRecipe').modal('show');

                     // Ambil nama menu/dapur dari baris tabel
                    const row = this.closest('tr');
                    const kitchenName = row.children[1].textContent;
                    const menuName = row.children[2].textContent;

                    // Set Action URL
                    form.action = `/dashboard/setup/racik-menu/${menuId}/${kitchenId}`;
                    document.getElementById('edit_kitchen_id').value = kitchenId;
                    document.getElementById('edit_menu_id').value = menuId;
                    document.getElementById('display_kitchen_name').value = kitchenName;
                    document.getElementById('display_menu_name').value = menuName;

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

                            if (isUsed) {
                                wrapper.querySelectorAll('.remove-bahan').forEach(delBtn => {
                                    delBtn.disabled = true;
                                    delBtn.style.opacity = '0.5';
                                });
                                wrapper.querySelectorAll('input, select').forEach(input => {
                                    input.readOnly = true;
                                    input.style.pointerEvents = 'none';
                                    input.classList.add('bg-light');
                                });
                            }
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
            
            return `
                <div class="form-row mb-3 bahan-group">
                    ${rowIdInput}
                    <div class="col-md-7">
                        <select name="bahan_baku_id[]" class="form-control" required>
                            ${options}
                        </select>
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
                            const menuName = data[0].menu ? data[0].menu.nama : '-';
                            
                            let rows = '';

                            data.forEach(item => {

                                rows += `
                                            <tr>
                                                <td>${item.bahan_baku ? item.bahan_baku.nama : '-'}</td>
                                            </tr>
                                        `;
                            });

                            // Tabel Detail tanpa kolom harga dan tanpa footer total
                            const html = `
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="140" class="py-1 pl-0">Dapur</th>
                                                <td class="py-1">: ${kitchenName}</td>
                                            </tr>
                                            <tr>
                                                <th width="140" class="py-1 pl-0">Menu</th>
                                                <td class="py-1">: ${menuName}</td>
                                            </tr>
                                        </table>
                                        <table class="table table-bordered table-striped">
                                            <thead>
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

            // --- LOGIC: Reset Form saat Modal Dibuka ---
            $('#modalAddRecipe').on('show.bs.modal', function() {
                const form = this.querySelector('form');
                if (form) {
                    // Reset semua input
                    form.reset();
                    
                    // Reset select dapur
                    const kitchenSelect = form.querySelector('.kitchen-select');
                    if (kitchenSelect) {
                        kitchenSelect.value = '';
                    }
                    
                    // Reset select menu
                    const menuSelect = form.querySelector('.menu-select');
                    if (menuSelect) {
                        menuSelect.innerHTML = '<option value="" disabled selected>Pilih Menu</option>';
                        menuSelect.disabled = false;
                        menuSelect.removeAttribute('readonly');
                    }
                    
                    // Reset bahan wrapper ke 1 baris
                    const bahanWrapper = form.querySelector('#bahan-wrapper-add');
                    if (bahanWrapper) {
                        const firstRow = bahanWrapper.querySelector('.bahan-group');
                        if (firstRow) {
                            bahanWrapper.innerHTML = '';
                            bahanWrapper.appendChild(firstRow.cloneNode(true));
                            // Reset select bahan di baris pertama
                            const firstBahanSelect = bahanWrapper.querySelector('select[name="bahan_baku_id[]"]');
                            if (firstBahanSelect) {
                                firstBahanSelect.innerHTML = '<option value="" disabled selected>Pilih Bahan</option>';
                            }
                            // Reset satuan
                            const firstSatuanText = bahanWrapper.querySelector('.satuan-text');
                            const firstSatuanId = bahanWrapper.querySelector('.satuan-id');
                            if (firstSatuanText) firstSatuanText.value = '';
                            if (firstSatuanId) firstSatuanId.value = '';
                            // Sembunyikan tombol hapus di baris pertama
                            const firstRemoveBtn = bahanWrapper.querySelector('.remove-bahan');
                            if (firstRemoveBtn) firstRemoveBtn.classList.add('d-none');
                        }
                    }
                }
            });

        });
    </script>
@endpush
