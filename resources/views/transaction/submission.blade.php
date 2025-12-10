@extends('adminlte::page')

@section('title', 'Bahan Baku')

@section('content_header')
    <h1>Pengajuan Menu</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddSubmission"
        text="Tambah Pengajuan Menu"   
    />
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Nama Dapur</th>
                        <th>Nama Menu</th>
                        <th>Porsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
    @foreach ($submission as $item)
    <tr>
        <td>{{ $item->kode }}</td>
        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
        <td>{{ $item->kitchen->nama ?? '-' }}</td>
        <td>{{ $item->menu->nama ?? '-' }}</td>
        <td>{{ number_format($item->porsi) }}</td>
        <td>
            <button type="button" 
                class="btn btn-primary btn-sm" 
                data-toggle="modal" 
                data-target="#modalDetail">
                Detail
            </button>

            <a href="{{ route('submissions.edit', $item->id) }}" class="btn btn-warning btn-sm">
                Edit
            </a>

            <x-button-delete 
                idTarget="#modalDeleteSubmission"
                formId="formDeleteSubmission"
                action="{{ route('submissions.destroy', $item->id) }}"
                text="Hapus"
            />
        </td>
    </tr>
    @endforeach
</tbody>

            </table>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <x-modal-form
        id="modalAddSubmission"
        title="Tambah Pengajuan Menu"
        action="#"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Kode</label>
            <input 
                id="kode_pengajuan" 
                type="text" 
                class="form-control" 
                name="kode" 
                readonly 
                required
            />
        </div>

        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" placeholder="Bawang Merah" class="form-control" name="tanggal" required>
        </div>
        
        <div class="form-group">
            <label>Nama Dapur</label>
            <select class="form-control" name="" required>
                <option value="" disabled selected>Pilih Dapur</option>
                <option value=""></option>
            </select>
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <select class="form-control" name="" required>
                <option value="" disabled selected>Pilih Menu</option>
                <option value=""></option>
            </select>
        </div>

        <div class="form-group">
            <label>Porsi</label>
            <input type="number" placeholder="55" class="form-control" name="porsi" required>
        </div>
    </x-modal-form>

    {{-- MODAL DETAIL --}}
    <x-modal-detail
        id="modalDetail"
        size="modal-lg"
        title="Detail Pengajuan Menu"
    >
        <div>
            <div>
                <p class="font-weight-bold mb-0">Nama Dapur:</p>
                <p>Dapur A Tembalang (Data Sampel)</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Nama Menu:</p>
                <p>Nasi Goreng (Data Sampel)</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Porsi:</p>
                <p>1000 (Data Sampel)</p>
            </div>
            <div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Jumlah Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bawang Merah (Data Sampel)</td>
                            <td>500 kg</td>
                        </tr>
                        <tr>
                            <td>Bawang Merah (Data Sampel)</td>
                            <td>500 kg</td>
                        </tr>
                        <tr>
                            <td>Bawang Merah (Data Sampel)</td>
                            <td>500 kg</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal-detail>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteSubmission"
        formId="formDeleteSubmission"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
    />
@endsection
