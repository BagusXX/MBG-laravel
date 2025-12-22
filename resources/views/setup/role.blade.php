@extends('adminlte::page')

@section('title', 'Role')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Role</h1>
@endsection

@section('content')
    <x-button-add 
        idTarget="#modalAddRecipe"
        text="Tambah Role"
    />

    <x-notification-pop-up />

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td width="90"></td>
                        <td></td>
                        <td width="220"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-form
        id="modalAddRecipe"
        title="Tambah Role"
        action="#"
        submitText="Simpan"
    >
        
    </x-modal-form>
@endsection