@extends('adminlte::page')

@section('title', 'Satuan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Satuan</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddUnit" 
        text="Tambah Satuan" 
    />

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Satuan</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $index => $unit)
                        <tr>
                            <td>{{ $units->firstItem() + $index }}</td>
                            <td>{{ $unit->satuan }}</td>
                            <td>{{ $unit->keterangan ?? '-' }}</td>
                            <td>
                                <button 
                                    type="button"
                                    class="btn btn-warning btn-sm btnEditUnit"
                                    data-id="{{ $unit->id }}"
                                    data-satuan="{{ $unit->satuan }}"
                                    data-keterangan="{{ $unit->keterangan }}"
                                    data-toggle="modal"
                                    data-target="#modalEditUnit"
                                >
                                    Edit
                                </button>
                                <x-button-delete 
                                    idTarget="#modalDeleteUnit"
                                    formId="formDeleteUnit"
                                    action="{{ route('master.unit.destroy', $unit->id) }}"
                                    text="Hapus" 
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data satuan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $units->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD SATUAN --}}
    <x-modal-form
        id="modalAddUnit"
        title="Tambah Satuan"
        action="{{ route('master.unit.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Satuan</label>
            <input type="text" placeholder="kg" class="form-control" name="satuan" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Keterangan (Opsional)</label>
            <input type="text" placeholder="Kilogram" class="form-control" name="keterangan" />
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form
        id="modalEditUnit"
        title="Edit Satuan"
        action=""
        submitText="Update"
    >
        @method('PUT')
        
        <div class="form-group">
            <label>Satuan</label>
            <input id="editSatuan" type="text" placeholder="kg" class="form-control" name="satuan" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Keterangan (Opsional)</label>
            <input id="editKeterangan" type="text" placeholder="Kilogram" class="form-control" name="keterangan" />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteUnit" 
        formId="formDeleteUnit" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.btnEditUnit').forEach(btn => {
                btn.addEventListener('click', function () {

                    const id = this.dataset.id;

                    // Isi field modal edit
                    document.getElementById('editSatuan').value = this.dataset.satuan;
                    document.getElementById('editKeterangan').value = this.dataset.keterangan;

                    // Set action form update
                    document.querySelector('#modalEditUnit form').action =
                        "{{ url('/dashboard/master/satuan') }}/" + id;
                });
            });

        });
    </script>
@endsection
