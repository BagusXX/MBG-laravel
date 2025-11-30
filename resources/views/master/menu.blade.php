@extends('adminlte::page')

@section('title', 'Nama Menu')

@section('content_header')
    <h1>Nama Menu</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddMenu"
        text="Tambah Nama Menu"   
    />
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Menu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Nasi Goreng</td>
                        <td>
                            <button class="btn btn-warning btn-sm">Edit</button>
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD MENU --}}
    <x-modal-form
        id="modalAddMenu"
        title="Tambah Nama Menu"
        action="#"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" placeholder="Mie Ayam" class="form-control" name="menu" required/>
        </div>
    </x-modal-form>
@endsection
