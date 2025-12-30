@extends('adminlte::page')

@section('title', 'Racik Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Racik Menu</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddRecipe"
        text="Racik Menu"   
    />

    <x-notification-pop-up />

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Dapur</th>
                        <th>Nama Menu</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recipes as $key => $recipe)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $recipe->kitchen->nama }}</td>
                            <td>{{ $recipe->menu->nama }}</td>
                            <td class="text-muted">-</td>
                            <td>
                                Rp {{ number_format($recipe->total_harga, 0, ',', '.') }}
                            </td>                            
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm" 
                                    data-toggle="modal" 
                                    data-target="#modalDetail{{ $recipe->id }}"
                                >
                                    Detail
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm btnEditRecipe"
                                    data-menu="{{ $recipe->menu_id }}"
                                    data-kitchen="{{ $recipe->kitchen_id }}"
                                    data-recipe="{{ $recipe->id }}"
                                    data-toggle="modal"
                                    data-target="#modalEditRecipe"
                                >
                                    Edit
                                </button>
                                <x-button-delete 
                                    idTarget="#modalDeleteRecipe"
                                    formId="formDeleteRecipe"
                                    action="{{ route('recipe.destroy', $recipe->id) }}"
                                    text="Hapus"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada racikan menu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FORM --}}
    <x-modal-form
        id="modalAddRecipe"
        size="modal-lg"
        title="Racik Menu"
        action="{{ route('recipe.store') }}"
        submitText="Simpan"
    >
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
            </select>
        </div>

        <div class="form-group">
            <div class="form-row mb-2">
                <div class="col-md-3 font-weight-bold">Bahan</div>
                <div class="col-md-2 font-weight-bold">Jumlah</div>
                <div class="col-md-3 font-weight-bold">Satuan</div>
                <div class="col-md-3 font-weight-bold">Harga</div>
                <div class="col-md-1"></div>
            </div>

            <div id="bahan-wrapper-add">
                <div class="form-row mb-3 bahan-group">
                    <div class="col-md-3">
                        <select name="bahan_baku_id[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Bahan</option>
                            @foreach ($bahanBaku as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="12" required>
                    </div>

                    <div class="col-md-3">
                        <!-- tampilkan nama satuan -->
                        <input type="text" class="form-control satuan-text" placeholder="Otomatis" readonly>

                        <!-- simpan ID satuan -->
                        <input type="hidden" name="satuan_id[]" class="satuan-id">


                    </div>
                    
                    <div class="col-md-3">
                        <input type="number" name="harga[]" class="form-control" placeholder="12000">
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none h-100 rounded-4xl" style="width: 65%">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-bahan-add" class="btn btn-outline-primary btn-block mt-2">
                <i class="fas fa-plus mr-1"></i>Tambah Bahan
            </button>

            

        </div>
    </x-modal-form>

    {{-- MODAL EDIT RECIPE --}}
    <x-modal-form
        id="modalEditRecipe"
        title="Edit Racik Menu"
        action=""
        submitText="Update"
    >
        @method('PUT')

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
            </select>
        </div>

        <div class="form-group">
            <div class="form-row mb-2">
                <div class="col-md-5 font-weight-bold">Bahan</div>
                <div class="col-md-2 font-weight-bold">Jumlah</div>
                <div class="col-md-4 font-weight-bold">Satuan</div>
                <div class="col-md-1"></div>
            </div>

            <div id="bahan-wrapper-edit">
                <div class="form-row mb-3 bahan-group">
                    <div class="col-md-5">
                        <select name="bahan_baku_id[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Bahan</option>
                            @foreach ($bahanBaku as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="12" required>
                    </div>

                    <div class="col-md-4">
                        <!-- tampilkan nama satuan -->
                        <input type="text" class="form-control satuan-text" placeholder="Otomatis" readonly>

                        <!-- simpan ID satuan -->
                        <input type="hidden" name="satuan_id[]" class="satuan-id">

                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none h-100" style="width: 100%">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-bahan-edit" class="btn btn-outline-primary btn-block mt-2">
                <i class="fas fa-plus mr-1"></i>Tambah Bahan
            </button>
        </div>
    </x-modal-form>

    {{-- MODAL DETAIL PER RECIPE --}}
    @foreach($recipes as $recipe)
        <x-modal-detail id="modalDetail{{ $recipe->id }}" size="modal-lg" title="Detail Menu">
            <table class="table table-borderless">
                <tr>
                    <th width="140" class="py-2">Dapur</th>
                    <td class="py-2">: {{ $recipe->kitchen->nama }}</td>
                </tr>
                <tr>
                    <th width="140" class="py-2">Nama Menu</th>
                    <td class="py-2">: {{ $recipe->menu->nama }}</td>
                </tr>
            </table>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Bahan Baku</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($recipe->bahan_baku)
                    <tr>
                        <td>{{ $recipe->bahan_baku->nama }}</td>
                        <td>{{ $recipe->jumlah }}</td>
                        <td>
                            Rp {{ number_format($recipe->bahan_baku->harga ?? 0, 0, ',', '.') }}
                        </td>
                        <td>
                            Rp {{ number_format(($recipe->bahan_baku->harga ?? 0) * $recipe->jumlah, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif

                </tbody>
            </table>
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="3" class="text-right">Total</td>
                    <td>
                        Rp {{ number_format($recipe->total_harga, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </x-modal-detail>
    @endforeach

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteRecipe"
        formId="formDeleteRecipe"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
    />
@endsection

@push('js')
    <script>
        window.BAHAN_LIST = @json($bahanBaku);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
                function initDynamicForm(wrapperId, addBtnId) {
                    const wrapper = document.getElementById(wrapperId);
                    const addBtn = document.getElementById(addBtnId);

                    if (!wrapper || !addBtn) return;

                    addBtn.addEventListener('click', function () {
                        const firstRow = wrapper.querySelector('.bahan-group');
                        const newRow = firstRow.cloneNode(true);

                        newRow.querySelectorAll('input, select').forEach(input => {
                            input.value = '';
                        });

                        const removeBtn = newRow.querySelector('.remove-bahan');
                        removeBtn.classList.remove('d-none');
                        removeBtn.addEventListener('click', () => newRow.remove());

                        wrapper.appendChild(newRow);
                    });
                }

                document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function () {
                    const action = this.getAttribute('data-action');
                    const form = document.getElementById('formDeleteRecipe');

                    if (form && action) {
                        form.setAttribute('action', action);
                    }
                });
            });

            document.querySelectorAll('.btnEditRecipe').forEach(btn => {
                btn.addEventListener('click', function () {
                    const menuId = this.dataset.menu;
                    const kitchenId = this.dataset.kitchen;
                    const recipeId = this.dataset.recipe;

                    const modal = document.getElementById('modalEditRecipe');
                    const form = modal.querySelector('form');

                    // set action PUT
                    form.action = `/dashboard/setup/racik-menu/${menuId}`;

                    // set kitchen
                    form.querySelector('.kitchen-select').value = kitchenId;

                    // load menu by kitchen
                    fetch(`/dashboard/setup/racik-menu/menus-by-kitchen/${kitchenId}`)
                        .then(res => res.json())
                        .then(menus => {
                            const menuSelect = form.querySelector('.menu-select');
                            menuSelect.innerHTML = '';

                            menus.forEach(m => {
                                const opt = document.createElement('option');
                                opt.value = m.id;
                                opt.textContent = m.nama;
                                if (m.id == menuId) opt.selected = true;
                                menuSelect.appendChild(opt);
                            });
                        });

                    // ambil detail bahan
                    fetch(`/dashboard/setup/racik-menu/detail/${menuId}/${kitchenId}`)
                        .then(res => res.json())
                        .then(items => {
                            console.log('HASIL DETAIL:', items);
                            const wrapper = document.getElementById('bahan-wrapper-edit');
                            wrapper.innerHTML = '';

                            if (!items.length) {
                                wrapper.innerHTML = `<p class="text-muted text-center">Belum ada bahan</p>`;
                                return;
                            }

                            items.forEach(item => {
                                const row = document.createElement('div');
                                row.className = 'form-row mb-3 bahan-group';

                                let options = '<option disabled>Pilih Bahan</option>';
                                window.BAHAN_LIST.forEach(b => {
                                    options += `
                                        <option value="${b.id}" ${b.id == item.bahan_baku_id ? 'selected' : ''}>
                                            ${b.nama}
                                        </option>`;
                                });

                                row.innerHTML = `
                                    <input type="hidden" name="row_id[]" value="${item.id}">
                                    <div class="col-md-5">
                                        <select name="bahan_baku_id[]" class="form-control" required>
                                            ${options}
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <input type="number" name="jumlah[]" class="form-control" value="${item.jumlah}">
                                    </div>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control satuan-text"
                                            value="${item.bahan_baku?.unit?.satuan ?? ''}" readonly>
                                    </div>

                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                `;

                                row.querySelector('.remove-bahan').onclick = () => row.remove();
                                wrapper.appendChild(row);
                            });
                        });
                });
            });


            document.querySelectorAll('.kitchen-select').forEach(kitchenSelect => {

            kitchenSelect.addEventListener('change', function () {
            const kitchenId = this.value;

            const form = this.closest('form');
            const menuSelect = form.querySelector('.menu-select');

            if (!menuSelect) return;

            menuSelect.innerHTML = '<option disabled selected>Loading...</option>';

            fetch(`/dashboard/setup/racik-menu/menus-by-kitchen/${kitchenId}`)
                .then(res => res.json())
                .then(data => {
                    menuSelect.innerHTML = '<option disabled selected>Pilih Menu</option>';

                    data.forEach(menu => {
                        const option = document.createElement('option');
                        option.value = menu.id;
                        option.textContent = menu.nama;
                        menuSelect.appendChild(option);
                    });
                })
                .catch(err => {
                    console.error(err);
                    menuSelect.innerHTML = '<option disabled>Gagal memuat menu</option>';
                });
        });

    });

        document.addEventListener('change', function (e) {
        if (!e.target.matches('select[name="bahan_baku_id[]"]')) return;

        const row = e.target.closest('.bahan-group');
        const bahanId = e.target.value;

        const hargaInput = row.querySelector('input[name="harga[]"]');
        const satuanIdInput = row.querySelector('.satuan-id');
        const satuanTextInput = row.querySelector('.satuan-text');

        fetch(`/dashboard/setup/racik-menu/bahan/${bahanId}`)
            .then(res => res.json())
            .then(data => {
                if (hargaInput) hargaInput.value = data.harga ?? 0;
                if (satuanIdInput) satuanIdInput.value = data.satuan_id ?? '';
                if (satuanTextInput) satuanTextInput.value = data.satuan ?? '';
            })
            
            .catch(console.error);
    });
            // Init untuk kedua modal
            initDynamicForm('bahan-wrapper-add', 'add-bahan-add');
            initDynamicForm('bahan-wrapper-edit', 'add-bahan-edit');
        });

    
    </script>
@endpush
