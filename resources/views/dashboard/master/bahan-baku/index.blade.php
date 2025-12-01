@extends('adminlte::page')

@section('title', 'Bahan Baku')

@section('content_header')
    <h1>Bahan Baku</h1>
@endsection

@section('content')

<x-button-add idTarget="#modalAddMaterials" text="Tambah Bahan Baku" />

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
    @forelse($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama }}</td>
            <td>{{ $item->stok }}</td>
            <td>{{ $item->satuan }}</td>
            <td>
                {{-- Tombol Hapus --}}
                <form action="{{ route('master.materials.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">Belum ada data</td>
        </tr>
    @endforelse
</tbody>

        </table>
    </div>
</div>

{{-- MODAL ADD MATERIALS --}}
<x-modal-form 
    id="modalAddMaterials" 
    title="Tambah Bahan Baku" 
    action="{{ route('master.materials.store') }}"
    submitText="Simpan"
>
    @csrf
    <div class="form-group">
        <label>Nama Bahan</label>
        <input type="text" placeholder="Bawang Merah" class="form-control" name="nama" required>
    </div>
    <div class="form-group">
        <label>Jumlah</label>
        <input type="number" placeholder="20" class="form-control" name="stok" required>
    </div>
    <div class="form-group">
        <label>Satuan</label>
        <select class="form-control" name="satuan" required>
            <option value="" disabled selected>Pilih Satuan</option>
            <option value="kg">Kilogram (kg)</option>
            <option value="g">Gram (g)</option>
            <option value="L">Liter (L)</option>
            <option value="mL">Mili Liter (mL)</option>
            <option value="pcs">Pieces (pcs)</option>
            <option value="pack">Pack</option>
            <option value="botol">Botol</option>
            <option value="bungkus">Bungkus</option>
        </select>
    </div>
</x-modal-form>

@endsection
