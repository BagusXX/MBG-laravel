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
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recipes as $key => $recipe)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $recipe->kitchen->nama }}</td>
                            <td>{{ $recipe->menu->nama }}</td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm" 
                                    data-toggle="modal" 
                                    data-target="#modalDetail{{ $recipe->id }}">
                                    Detail
                                </button>
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

    {{-- MODAL ADD RECIPE --}}
    <x-modal-form
        id="modalAddRecipe"
        title="Racik Menu"
        action="{{ route('recipe.store') }}"
        submitText="Simpan"
    >
        @csrf

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

            <div id="bahan-wrapper">
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
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-bahan" class="btn btn-outline-primary btn-block mt-2">
                <i class="fas fa-plus mr-1"></i>Tambah Bahan
            </button>
        </div>
    </x-modal-form>

    {{-- MODAL DETAIL PER RECIPE --}}
    @foreach($recipes as $recipe)
        <x-modal-detail id="modalDetail{{ $recipe->id }}" size="modal-lg" title="Detail Menu">
            <div>
                <p><strong>Dapur:</strong> {{ $recipe->kitchen->nama }}</p>
                <p><strong>Nama Menu:</strong> {{ $recipe->menu->nama }}</p>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipe->bahanBaku as $b)
                            <tr>
                                <td>{{ $b->nama }}</td>
                                <td>{{ $b->pivot->jumlah }} {{ $b->pivot->satuan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-modal-detail>
    @endforeach
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('bahan-wrapper');
    const addBtn = document.getElementById('add-bahan');

    addBtn.addEventListener('click', function () {
        const firstRow = wrapper.querySelector('.bahan-group');
        const newRow = firstRow.cloneNode(true);

        // Reset value input/select
        newRow.querySelectorAll('input, select').forEach(input => {
            input.value = '';
        });

        // Tampilkan tombol hapus
        const removeBtn = newRow.querySelector('.remove-bahan');
        removeBtn.classList.remove('d-none');

        // Tambahkan event hapus
        removeBtn.addEventListener('click', function () {
            newRow.remove();
        });

        // Tambahkan row baru
        wrapper.appendChild(newRow);
    });

    // Event hapus untuk row pertama (opsional)
    const firstRemoveBtn = wrapper.querySelector('.remove-bahan');
    if(firstRemoveBtn){
        firstRemoveBtn.addEventListener('click', function () {
            firstRemoveBtn.closest('.bahan-group').remove();
        });
    }
});
</script>
@endpush
