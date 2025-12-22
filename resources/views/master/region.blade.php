@extends('adminlte::page')

@section('title', 'Region')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Region</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddRegion"
        text="Tambah Region"
    />

    {{-- ALERT SUCCESS --}}
    {{-- @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif --}}
    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Region</th>
                        <th>Penanggung Jawab</th>
                        {{-- <th>Region</th>
                        <th>Kontak Person</th>
                        <th>Nomor</th> --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $index => $region)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $region->kode_region }}</td>
                            <td>{{ $region->nama_region }}</td>
                            <td>{{ $region->penanggung_jawab }}</td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning btnEditRegion"
                                    data-toggle="modal"
                                    data-target="#modalEditRegion"
                                >
                                    Edit    
                                </button>
                                
                                <x-button-delete 
                                    idTarget="#modalDeleteRegion" 
                                    formId="formDeleteRegion"
                                    action="{{ route('master.region.destroy', $region->id) }}"
                                    text="Hapus" 
                                />

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada Region</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD REGION --}}
    <x-modal-form
    id="modalAddRegion"
    title="Tambah Region"
    {{-- action="#" --}}
    action="{{ route('master.region.store') }}"
    submitText="Simpan"
>
        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode_region" 
                id="kode_region"
                class="form-control"
                value="{{ $nextKode }}" 
                readonly 
                required 
            />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama_region">Nama Region</label>
            <input id="nama_region" type="text" name="nama_region" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="penanggung_jawab">Penanggung Jawab</label>
            <input id="penanggung_jawab" type="text" name="penanggung_jawab" class="form-control" required />
        </div>
    </x-modal-form>

    {{-- MODAL EDIT REGION --}}
    <x-modal-form
        id="modalEditRegion"
        title="Edit Region"
        action="{{ route('master.region.update', $region->id) }}"
        submitText="Update"
    >
        @method('PUT')
    <input type="hidden" name="id" id="edit_id">

        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode_region" 
                class="form-control" 
                id="kode_region"
                readonly
                required />
        </div>

        <div class="form-group">
            <label>Nama Region</label>
            <input type="text" id="nama_region" name="nama_region" class="form-control" required />
        </div>
        
        <div class="form-group">
            <label>Penanggung Jawab</label>
            <input type="text" id="penanggung_jawab" name="penanggung_jawab" class="form-control" required />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteRegion" 
        formId="formDeleteRegion" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />  
@endsection
