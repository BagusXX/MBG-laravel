@extends('adminlte::page')

@section('title', 'Data Dapur')

@section('content_header')
    <h1>Data Dapur</h1>
@endsection

@section('content')

<x-button-add idTarget="#modalAddKitchen" text="Tambah Dapur" />

@if(session('success'))
    <div class="alert alert-success mt-2">{{ session('success') }}</div>
@endif

<div class="card mt-2">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Dapur</th>
                    <th>Alamat</th>
                    <th>Kepala Dapur</th>
                    <th>Nomor Kepala Dapur</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>

                @forelse($kitchens as $index => $k)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td></td>
                        <td>{{ $k->nama }}</td>
                        <td>{{ $k->alamat }}</td>
                        <td>{{ $k->kepala_dapur }}</td>
                        <td>{{ $k->nomor_kepala_dapur }}</td>
                        <td>
                            <button 
                                class="btn btn-warning btn-sm btnEditKitchen"
                                data-id="{{ $k->id }}"
                                data-nama="{{ $k->nama }}"
                                data-alamat="{{ $k->alamat }}"
                                data-kepala="{{ $k->kepala_dapur }}"
                                data-nomor="{{ $k->nomor_kepala_dapur }}"
                                data-toggle="modal"
                                data-target="#modalEditKitchen"
                            >Edit</button>
                            <button class="btn btn-danger btn-sm" data-delete-target="#modalDeleteKitchen" data-action="#" data-form-id="formDeleteKitchen">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Belum ada data dapur</td></tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>

{{-- MODAL ADD --}}
<x-modal-form
    id="modalAddKitchen"
    title="Tambah Dapur"
    action="{{ route('master.kitchen.store') }}"
    submitText="Simpan"
>
    @csrf
    <div class="form-group">
        <label>Kode</label>
        <input type="text" name="kode" class="form-control" required>
    </div>
   
    <div class="form-group">
        <label>Nama Dapur</label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <div class="form-group mt-2">
        <label>Alamat</label>
        <input type="text" name="alamat" class="form-control" required>
    </div>

    <div class="form-group mt-2">
        <label>Nama Kepala Dapur</label>
        <input type="text" name="kepala_dapur" class="form-control" required>
    </div>

    <div class="form-group mt-2">
        <label>Nomor Kepala Dapur</label>
        <input type="text" name="nomor_kepala_dapur" class="form-control" required>
    </div>
</x-modal-form>

{{-- MODAL EDIT --}}
<x-modal-form
    id="modalEditKitchen"
    title="Edit Dapur"
    action=""
    submitText="Update"
>
    @method('PUT')

    <div class="form-group">
        <label>Nama Dapur</label>
        <input type="text" id="editNama" name="nama" class="form-control" required>
    </div>

    <div class="form-group mt-2">
        <label>Alamat</label>
        <input type="text" id="editAlamat" name="alamat" class="form-control" required>
    </div>

    <div class="form-group mt-2">
        <label>Nama Kepala Dapur</label>
        <input type="text" id="editKepala" name="kepala_dapur" class="form-control" required>
    </div>

    <div class="form-group mt-2">
        <label>Nomor Kepala Dapur</label>
        <input type="text" id="editNomor" name="nomor_kepala_dapur" class="form-control" required>
    </div>
</x-modal-form>

<x-modal-delete 
    id="modalDeleteKitchen"
    formId="formDeleteKitchen"
    title="Konfirmasi Hapus"
    message="Apakah Anda yakin ingin menghapus data ini?"
    confirmText="Hapus">
</x-modal-delete>

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.btnEditKitchen').forEach(btn => {
            btn.addEventListener('click', function () {

                const id = this.dataset.id;

                // Isi field modal edit
                document.getElementById('editNama').value = this.dataset.nama;
                document.getElementById('editAlamat').value = this.dataset.alamat;
                document.getElementById('editKepala').value = this.dataset.kepala;
                document.getElementById('editNomor').value = this.dataset.nomor;

                // Set action form update
                document.querySelector('#modalEditKitchen form').action =
                    "{{ url('/dashboard/master/dapur') }}/" + id;
            });
        });

    });
</script>
@endsection
