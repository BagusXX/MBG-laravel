@extends('adminlte::page')

@section('title', 'Operational')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Operasional</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddOperasional"
        text="Tambah Biaya Operasional"
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
                        <th>Nama Biaya</th>
                        <th>Harga</th>
                        <th>Tempat Beli</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operationals as $index => $operational)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $operational->kode }}</td>
                            <td>{{ $operational->nama }}</td>
                            <td>{{ $operational->harga }}</td>
                            <td>{{ $operational->tempat_beli }}</td>
                            <td>{{ $operational->updated_at }}</td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning"
                                    onclick="editOperational(this)"
                                    data-id="{{ $operational->id }}"
                                    data-kode="{{ $operational->kode }}"
                                    data-nama="{{ $operational->nama }}"
                                    data-harga="{{ $operational->harga }}"
                                    data-tempat_beli="{{ $operational->tempat_beli }}"
                                    data-tanggal="{{ $operational->created_at->format('Y-m-d') }}"
                                >
                                    Edit    
                                </button>
                                {{-- Tombol Hapus --}}
                                <x-button-delete 
                                    idTarget="#modalDeleteOperational" 
                                    formId="formDeleteOperational"
                                    action="{{ route('master.operational.destroy', $operational->id) }}"
                                    text="Hapus" 
                                />

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada Biaya Operational</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD OPERATIONAL --}}
    <x-modal-form
    id="modalAddOperasional"
    title="Tambah Biaya Operasional"
    action="{{ route('master.operational.store') }}"
    submitText="Simpan"
>
        <div class="form-group">
            <label>Kode Transaksi</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                id="kode_operasional"
                value="{{ $nextKode }}"
                readonly 
                required 
            />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama_operasional">Nama Biaya</label>
            <input id="nama_operasional" type="text" name="nama" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" placeholder="0" required />
        </div>
        <div class="form-group mt-2">
            <label>Tempat Beli</label>
            <textarea name="tempat_beli" class="form-control" rows="2" placeholder="Detail pengeluaran..."></textarea>
        </div>
        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required />
        </div>
        
    </x-modal-form>

    {{-- MODAL EDIT OPERATIONAL --}}
    <x-modal-form
        id="modalEditOperasional"
        title="Edit Operasional"
        action=""
        submitText="Update"
    >
        @method('PUT')
        <input type="hidden" name="id" id="edit_id">

        <div class="form-group">
            <label>Kode Transaksi</label>
            <input 
                type="text" 
                name="kode" 
                id="edit_kode"
                class="form-control"
                readonly
            />
        </div>

        <div class="form-group mt-2">
            <label>Nama Biaya</label>
            <input 
                type="text" 
                name="nama" 
                id="edit_nama"
                class="form-control" 
                required
            />
        </div>

        <div class="form-group mt-2">
            <label>Harga</label>
            <input 
                type="number" 
                name="harga" 
                id="edit_harga"
                class="form-control" 
                required
            />
        </div>

        <div class="form-group mt-2">
            <label>Tempat Beli</label>
            <textarea 
                name="tempat_beli" 
                id="edit_tempat_beli"
                class="form-control"
            ></textarea>
        </div>

        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input 
                type="date" 
                name="tanggal" 
                id="edit_tanggal"
                class="form-control" 
                required
            />
        </div>

    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteOperational" 
        formId="formDeleteOperational" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />  
@endsection

@section('js')
<script>
function editOperational(button) {
    const id = button.dataset.id;

    const form = document.querySelector('#modalEditOperasional form');
    form.action = `/dashboard/master/operational/${id}`;

    // isi input
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_kode').value = button.dataset.kode;
    document.getElementById('edit_nama').value = button.dataset.nama;
    document.getElementById('edit_harga').value = button.dataset.harga;
    document.getElementById('edit_tempat_beli').value = button.dataset.tempat_beli;
    document.getElementById('edit_tanggal').value = button.dataset.tanggal;

    // tampilkan modal
    $('#modalEditOperasional').modal('show');
}

function capitalizeWords(text) {
    return text
        .toLowerCase()
        .replace(/\b\w/g, char => char.toUpperCase());
}

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('input[name="nama"]');

    inputs.forEach(input => {
        input.addEventListener('input', function () {
            this.value = capitalizeWords(this.value);
        });
    });
});
</script>
@endsection