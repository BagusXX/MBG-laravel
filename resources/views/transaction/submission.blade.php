@extends('adminlte::page')

@section('title', 'Bahan Baku')

@section('content_header')
    <h1>Pengajuan Menu</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddRecipe"
        text="Tambah Pengajuan Menu"   
    />
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Nama Menu</th>
                        <th>Porsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>A01</td>
                        <td>Senin, 24 November 2025</td>
                        <td>Nasi Goreng</td>
                        <td>1000</td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm">Detail</button>
                            <button type="button" class="btn btn-warning btn-sm">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
