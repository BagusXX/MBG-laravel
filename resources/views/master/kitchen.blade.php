@extends('adminlte::page')

@section('title', 'Data Dapur')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Dapur</h1>
@endsection

@section('content')
    @can('master.kitchen.create')
        <x-button-add idTarget="#modalAddKitchen" text="Tambah Dapur" />
    @endcan

    <x-notification-pop-up />

    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Dapur</th>
                        <th>Region</th>
                        <th>Kota</th>
                        <th>Alamat</th>
                        <th>Kepala Dapur</th>
                        <th>Nomor Kepala Dapur</th>
                        @canany(['master.kitchen.update', 'master.kitchen.delete'])
                            <th>Aksi</th>
                        @endcanany()
                    </tr>
                </thead>
                <tbody>

                    @forelse($kitchens as $index => $k)
                        <tr>
                            <td>{{ $kitchens->firstItem() + $index }}</td>
                            <td>{{ $k->kode }}</td>
                            <td>{{ $k->nama }}</td>
                            <td>{{ $k->region->nama_region }}</td>
                            <td>{{ $k->kota }}</td>
                            <td>{{ $k->alamat }}</td>
                            <td>{{ $k->kepala_dapur }}</td>
                            <td>{{ $k->nomor_kepala_dapur }}</td>
                            @canany(['master.kitchen.update', 'master.kitchen.delete'])
                                <td>
                                    @can('master.kitchen.update')
                                        <button type="button" class="btn btn-warning btn-sm btnEditKitchen"
                                            data-id="{{ $k->id }}" data-nama="{{ $k->nama }}"
                                            data-alamat="{{ $k->alamat }}" data-kota="{{ $k->kota }}"
                                            data-region="{{ $k->region_id }}" data-kepala="{{ $k->kepala_dapur }}"
                                            data-nomor="{{ $k->nomor_kepala_dapur }}" data-toggle="modal"
                                            data-target="#modalEditKitchen">Edit</button>
                                    @endcan


                                    @can('master.kitchen.delete')
                                        <x-button-delete idTarget="#modalDeleteKitchen" formId="formDeleteKitchen"
                                            action="{{ route('master.kitchen.destroy', $k->id) }}" text="Hapus" />
                                    @endcan

                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data dapur</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $kitchens->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <x-modal-form id="modalAddKitchen" title="Tambah Dapur" action="{{ route('master.kitchen.store') }}"
        submitText="Simpan">
        <div class="form-group">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" value="{{ $kodeBaru }}" readonly required>
        </div>

        <div class="form-group">
            <label>Nama Dapur</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="form-group mt-2">
            <label> Region </label>
            <select name="region_id" class="form-control" required>
                <option value="">-- Pilih Region --</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->nama_region }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-2">
            <label>Kota</label>
            <input type="text" name="kota" class="form-control" required>
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
    <x-modal-form id="modalEditKitchen" title="Edit Dapur" action="" submitText="Update">
        @method('PUT')

        <div class="form-group">
            <label>Nama Dapur</label>
            <input type="text" id="editNama" name="nama" class="form-control" required>
        </div>

        <div class="form-group mt-2">
            <label>Region</label>
            <select id="editRegion" name="region_id" class="form-control" required>
                <option value="">-- Pilih Region --</option>
                @foreach ($regions as $r)
                    <option value="{{ $r->id }}">{{ $r->nama_region }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-2">
            <label>Kota</label>
            <input type="text" id="editKota" name="kota" class="form-control" required>
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

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeleteKitchen" formId="formDeleteKitchen" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus">
    </x-modal-delete>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.btnEditKitchen').forEach(btn => {
                btn.addEventListener('click', function() {

                    const id = this.dataset.id;

                    // Isi field modal edit
                    document.getElementById('editNama').value = this.dataset.nama;
                    document.getElementById('editKota').value = this.dataset.kota;
                    document.getElementById('editAlamat').value = this.dataset.alamat;
                    document.getElementById('editKepala').value = this.dataset.kepala;
                    document.getElementById('editNomor').value = this.dataset.nomor;
                    document.getElementById('editRegion').value = this.dataset.region;

                    // Set action form update
                    document.querySelector('#modalEditKitchen form').action =
                        "{{ url('/dashboard/master/dapur') }}/" + id;
                });
            });
        });
    </script>
@endsection
