@extends('adminlte::page')

@section('title', 'Dapur')

@section('content_header')
    <h1>Dapur</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddKitchen"
        text="Tambah Data Dapur"   
    />
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Dapur</th>
                        <th>Alamat</th>
                        <th>Kepala Dapur</th>
                        <th>No. HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Dapur A Tembalang</td>
                        <td>Jalan Pahlawan No. 83</td>
                        <td>Joko Anwar</td>
                        <td>085724409045</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD KITCHEN --}}
    <x-modal-form
        id="modalAddKitchen"
        title="Tambah Dapur"
        action="#"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Dapur</label>
            <input type="text" placeholder="Dapur Cita Rasa Tembalang" class="form-control" name="dapur" required/>
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" placeholder="Jalan Pemuda No. 75" class="form-control" name="alamat" required/>
        </div>
        <div class="form-group">
            <label>Kepala Dapur</label>
            <input type="text" placeholder="Iko Uwais" class="form-control" name="kepala" required/>
        </div>
        <div class="form-group">
            <label>No. HP</label>
            <input type="text" placeholder="085732208835" class="form-control" name="nomor" required/>
        </div>
    </x-modal-form>
@endsection
