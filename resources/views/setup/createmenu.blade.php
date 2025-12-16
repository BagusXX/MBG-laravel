@extends('adminlte::page')

@section('title', 'Racik Menu')

@section('content_header')
    <h1>Racik Menu</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddRecipe"
        text="Racik Menu"   
    />

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Dapur</th>
                        <th>Nama Menu</th>
                        <th>Porsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recipes as $key => $recipe)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $recipe->kitchen->nama }}</td>
                            <td>{{ $recipe->menu->nama }}</td>
                            <td>{{ $recipe->porsi }}</td>
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
                                    data-toggle="modal"
                                    data-target="#modalEditRecipe"
                                >
                                    Edit
                                </button>
                                <x-button-delete 
                                    idTarget="#modalDeleteRecipe"
                                    formId="formDeleteRecipe"
                                    action="#"
                                    text="Hapus"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada racikan menu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FORM --}}
    <x-modal-form
        id="modalAddRecipe"
        title="Racik Menu"
        action="{{ route('recipe.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Dapur</label>
            <select class="form-control" name="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach ($kitchens as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <select class="form-control" name="menu_id" required>
                <option value="" disabled selected>Pilih Menu</option>
                @foreach ($menus as $m)
                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Porsi</label>
            <input type="number" name="porsi" class="form-control" placeholder="12">
        </div>

        {{-- <div class="col-md-5">
            <input type="number" name="porsi" class="form-control" placeholder="12">
        </div> --}}

        <div class="form-group">
            <div class="form-row mb-2">
                <div class="col-md-5 font-weight-bold">Bahan</div>
                <div class="col-md-2 font-weight-bold">Jumlah</div>
                <div class="col-md-4 font-weight-bold">Satuan</div>
                <div class="col-md-1"></div>
            </div>

            <div id="bahan-wrapper-add">
                <div class="form-row mb-3 bahan-group">
                    <div class="col-md-5">
                        <select name="bahan[]" class="form-control" required>
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
                        <select name="satuan[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Satuan</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->satuan }}">{{ $u->satuan }}</option>
                            @endforeach
                        </select>
                    </div>

                    

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none h-100" style="width: 100%">
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
            <select class="form-control" name="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach ($kitchens as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <select class="form-control" name="menu_id" required>
                <option value="" disabled selected>Pilih Menu</option>
                @foreach ($menus as $m)
                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                @endforeach
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
                        <select name="bahan[]" class="form-control" required>
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
                        <select name="satuan[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Satuan</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->satuan }}">{{ $u->satuan }}</option>
                            @endforeach
                        </select>
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
            <div>
                <div>
                    <p class="font-weight-bold mb-0">Dapur:</p>
                    <p>{{ $recipe->kitchen->nama }}</p>
                </div>
                <div>
                    <p class="font-weight-bold mb-0">Nama Menu:</p>
                    <p>{{ $recipe->menu->nama }}</p>
                </div>

                <div>
                <p class="font-weight-bold mb-0">Porsi:</p>
                    <p>{{ $recipe->porsi }}</p>

                </div>
                
                <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Bahan Baku</th>
            <th>Jumlah</th>
            <th>Total dengan porsi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($recipe->bahanBaku as $b)
            <tr>
                <td>{{ $b->nama }}</td>
                <td>{{ $b->pivot->jumlah }} {{ $b->pivot->satuan }}</td>
                <td>{{ $b->pivot->jumlah * $recipe->porsi }} {{ $b->pivot->satuan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

            </div>
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
        // document.addEventListener('DOMContentLoaded', function () {
        //     const wrapper = document.getElementById('bahan-wrapper');
        //     const addBtn = document.getElementById('add-bahan');

        //     addBtn.addEventListener('click', function () {
        //         const firstRow = wrapper.querySelector('.bahan-group');
        //         const newRow = firstRow.cloneNode(true);

        //         // Reset value input/select
        //         newRow.querySelectorAll('input, select').forEach(input => {
        //             input.value = '';
        //         });

        //         // Tampilkan tombol hapus
        //         const removeBtn = newRow.querySelector('.remove-bahan');
        //         removeBtn.classList.remove('d-none');

        //         // Tambahkan event hapus
        //         removeBtn.addEventListener('click', function () {
        //             newRow.remove();
        //         });

        //         // Tambahkan row baru
        //         wrapper.appendChild(newRow);
        //     });

        //     // Event hapus untuk row pertama (opsional)
        //     const firstRemoveBtn = wrapper.querySelector('.remove-bahan');
        //     if(firstRemoveBtn){
        //         firstRemoveBtn.addEventListener('click', function () {
        //             firstRemoveBtn.closest('.bahan-group').remove();
        //         });
        //     }
        // });

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

            // Init untuk kedua modal
            initDynamicForm('bahan-wrapper-add', 'add-bahan-add');
            initDynamicForm('bahan-wrapper-edit', 'add-bahan-edit');
        });
    </script>
@endpush
