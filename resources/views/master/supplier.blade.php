@extends('adminlte::page')

@section('title', 'Supplier')

@section('content_header')
    <h1>Supplier</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddSupplier"
        text="Tambah Supplier"
    />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Kontak Person</th>
                        <th>Nomor</th>
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
                        <td></td>
                        <td>
                            <x-button-delete 
                                idTarget="#modalDeleteSupplier" 
                                formId="formDeleteSupplier"
                                action="#"
                                text="Hapus" 
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD SUPPLIER --}}
    <x-modal-form
        id="modalAddSupplier"
        title="Tambah Supplier"
        action="#"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                readonly 
                required 
            />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama">Nama</label>
            <input id="nama" type="text" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama">Alamat</label>
            <input id="nama" type="text" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama">Kontak Person</label>
            <input id="nama" type="text" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama">Nomor</label>
            <input id="nama" type="text" name="nama" class="form-control" required />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteSupplier" 
        formId="formDeleteSupplier" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />  

@endsection
