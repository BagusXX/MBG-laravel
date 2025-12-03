@extends('adminlte::page')

@section('title', 'Nama Menu')

@section('content_header')
    <h1>Nama Menu</h1>
@endsection

@section('content')
    {{-- Tombol Tambah --}}
    <x-button-add
        idTarget="#modalAddMenu"
        text="Tambah Nama Menu"
    />

    {{-- Alert sukses --}}
    @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Menu</th> {{-- Tambah kolom kode menu --}}
                        <th>Nama Menu</th>
                        <th>Dapur</th> {{-- Tampilkan dapur terkait --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $index => $menu)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $menu->kode }}</td> {{-- Kode menu --}}
                            <td>{{ $menu->nama }}</td>
                            <td>{{ $menu->kitchen->nama ?? '-' }}</td> {{-- Nama dapur --}}
                            <td>
                                <button 
                                    class="btn btn-danger btn-sm" 
                                    data-delete-target="#modalDeleteMenu" 
                                    data-action="{{ route('master.menu.destroy', $menu->id) }}" 
                                    data-form-id="formDeleteMenu">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada menu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD MENU --}}
    <x-modal-form
        id="modalAddMenu"
        title="Tambah Nama Menu"
        action="{{ route('master.menu.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" placeholder="Mie Ayam" class="form-control" name="nama" required/>
        </div>

        <div class="form-group mt-2">
            <label>Pilih Dapur</label>
            <select name="kitchen_id" class="form-control" required>
                <option value="" disabled selected>-- Pilih Dapur --</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }} ({{ $kitchen->kode }})</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteMenu"
        formId="formDeleteMenu"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus menu ini?"
        confirmText="Hapus">
    </x-modal-delete>
@endsection
