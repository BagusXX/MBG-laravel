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
                        <th>Nama Menu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $index => $menu)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $menu->nama }}</td>
                            <td>
                                <form action="{{ route('master.menu.destroy', $menu->id) }}"
      method="POST"
      style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
</form>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada menu</td>
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
        @csrf
        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" placeholder="Mie Ayam" class="form-control" name="nama" required/>
        </div>
    </x-modal-form>
@endsection
