@extends('adminlte::page')

@section('title', 'Region')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Region</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    @if($canManage)
    <x-button-add 
        idTarget="#modalAddRegion"
        text="Tambah Region"
    />
    @endif

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama Region</th>
                        <th>Penanggung Jawab</th>
                        {{-- <th>Region</th>
                        <th>Kontak Person</th>
                        <th>Nomor</th> --}}
                        @if($canManage)
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $index => $region)
                        <tr>
                            <td>{{ $regions->firstItem() + $index }}</td>
                            <td>{{ $region->kode_region }}</td>
                            <td>{{ $region->nama_region }}</td>
                            <td>{{ $region->penanggung_jawab }}</td>
                            @if($canManage)
                            <td>
                                <button 
                                class="btn btn-warning btn-sm"
                                onclick="editRegion(this)"
                                data-id="{{ $region->id }}"
                                data-kode="{{ $region->kode_region }}"
                                data-nama="{{ $region->nama_region }}"
                                data-pj="{{ $region->penanggung_jawab }}"
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
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada Region</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $regions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD REGION --}}
    <x-modal-form
    id="modalAddRegion"
    title="Tambah Region"
    action="{{ route('master.region.store') }}"
    submitText="Simpan"
>
        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode_region" 
                id="add_kode_region"
                class="form-control"
                value="{{ $nextKode }}" 
                readonly 
                required 
            />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama_region">Nama Region</label>
            <input id="add_nama_region" type="text" name="nama_region" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="penanggung_jawab">Penanggung Jawab</label>
            <input id="add_penanggung_jawab" type="text" name="penanggung_jawab" class="form-control" required />
        </div>
    </x-modal-form>

    {{-- MODAL EDIT REGION --}}
    <x-modal-form
        id="modalEditRegion"
        title="Edit Region"
        action=""
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
                id="edit_kode_region"
                readonly
                required />
        </div>

        <div class="form-group">
            <label>Nama Region</label>
            <input type="text" id="edit_nama_region" name="nama_region" class="form-control" required />
        </div>
        
        <div class="form-group">
            <label>Penanggung Jawab</label>
            <input type="text" id="edit_penanggung_jawab" name="penanggung_jawab" class="form-control" required />
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

<script>
function editRegion(button) {
    const id = button.dataset.id;

    const form = document.querySelector('#modalEditRegion form');
    form.action = `/dashboard/master/region/${id}`;

    // isi input
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_kode_region').value = button.dataset.kode;
    document.getElementById('edit_nama_region').value = button.dataset.nama;
    document.getElementById('edit_penanggung_jawab').value = button.dataset.pj;

    // tampilkan modal
    $('#modalEditRegion').modal('show');
}
</script>

