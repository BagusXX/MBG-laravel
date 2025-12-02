{{-- @extends('adminlte::page')

@section('title', 'Bahan Baku')

@section('content_header')
    <h1>Bahan Baku</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddMaterials"
        text="Tambah Bahan Baku"   
    />
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Bahan</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <button class="btn btn-warning btn-sm">Edit</button>
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    MODAL ADD MATERIALS
    <x-modal-form 
        id="modalAddMaterials" 
        title="Tambah Bahan Baku" 
        action="#"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Bahan</label>
            <input type="text" placeholder="Bawang Merah" class="form-control" name="nama" required>
        </div>
        <div class="form-group">
            <label>Jumlah</label>
            <input type="number" placeholder="20" class="form-control" name="jumlah" required>
        </div>
        <div class="form-group">
            <label>Satuan</label>
            <select class="form-control" name="satuan" required>
                <option value="" disabled selected>Pilih Satuan</option>
                <option value="kg">Kilogram (kg)</option>
                <option value="g">Gram (g)</option>
                <option value="L">Liter (L)</option>
                <option value="mL">Mili Liter (mL)</option>
                <option value="pcs">Pieces (pcs)</option>
                <option value="pack">Pack</option>
                <option value="botol">Botol</option>
                <option value="bungkus">Bungkus</option>
            </select>
        </div>
    </x-modal-form>

</div>

@endsection --}}
