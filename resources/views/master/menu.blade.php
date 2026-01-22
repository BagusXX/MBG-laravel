@extends('adminlte::page')

@section('title', 'Nama Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Nama Menu Dapur </h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <x-button-add
                    idTarget="#modalAddMenu"
                    text="Tambah Nama Menu"
                />
        </div>
        <div class="col-md-6">
            <form action="{{ route('master.menu.index') }}" method="GET">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari nama menu atau kode..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('master.menu.index') }}" class="btn btn-danger">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
            </form>
        </div>
    </div>
    
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
                        <th>Kode Menu</th> {{-- Tambah kolom kode menu --}}
                        <th>Dapur</th>
                        <th>Nama Menu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td>{{ $item->kode }}</td> {{-- Kode menu --}}
                            <td>{{ $item->kitchen->nama ?? '-' }}</td> 
                            <td>{{ $item->nama }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm btnEditMenu"
                                    data-id="{{ $item->id }}"
                                    data-kode="{{ $item->kode }}"
                                    data-nama="{{ $item->nama }}"
                                    data-dapur-id="{{ $item->kitchen_id }}"
                                    data-old-kode="{{ $item->kode }}"
                                    data-old-dapur-id="{{ $item->kitchen_id }}"
                                    data-toggle="modal"
                                    data-target="#modalEditMenu"

                                >
                                    Edit
                                </button>
                                <x-button-delete 
                                    idTarget="#modalDeleteMenu"
                                    formId="formDeleteMenu"
                                    action="{{ route('master.menu.destroy', $item->id) }}"
                                    text="Hapus"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada menu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $items->links('pagination::bootstrap-4') }}
            </div>
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
            <label>Kode</label>
            <input id="kode_menu" type="text" class="form-control" name="kode" readonly required/>
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" placeholder="Mie Ayam" class="form-control" name="nama" required/>
        </div>

        <div class="form-group mt-2">
            <label>Pilih Dapur</label>
            <select name="kitchen_id" class="form-control" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }} ({{ $kitchen->kode }})</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form
        id="modalEditMenu"
        title="Edit Menu"
        action=""
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Kode</label>
            <input
                id="editKodeMenu"
                type="text" 
                class="form-control"
                name="kode"
                readonly
                required
            />
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <input
                id="editMenu"
                type="text" 
                class="form-control" 
                name="nama" 
                required/>
        </div>

        <div class="form-group">
            <label>Dapur</label>
            <select id="editDapur" class="form-control" name="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
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
        message="Apakah Anda yakin ingin menghapus Data ini?"
        confirmText="Hapus" 
    />
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const kodeInput = document.getElementById('kode_menu');
            const kitchenSelect = document.querySelector('select[name="kitchen_id"]');

            const generatedCodes = @json($generatedCodes);

            kitchenSelect.addEventListener('change', function () {
                const kitchenId = this.value;
                kodeInput.value = generatedCodes[kitchenId] || "";
            });

            let oldKitchenId = null;
            let oldKode = null;

            document.querySelectorAll('.btnEditMenu').forEach(btn => {
                btn.addEventListener('click', function () {

                    const id = this.dataset.id;

                    // Simpan dapur lama & kode lama
                    oldKitchenId = this.dataset.oldDapurId;
                    oldKode = this.dataset.oldKode;

                    // Isi field pertama kali
                    document.getElementById('editKodeMenu').value = oldKode;
                    document.getElementById('editMenu').value = this.dataset.nama;
                    document.getElementById('editDapur').value = oldKitchenId;

                    // Update action
                    document.querySelector('#modalEditMenu form').action =
                    "{{ url('/dashboard/master/nama-menu') }}/" + id;

                });
            });

            // Ubah kode ketika dapur berubah
            document.getElementById('editDapur').addEventListener('change', function () {
                const selectedKitchenId = this.value;

                // Jika user memilih kembali dapur awal → kembalikan kode lama
                if (selectedKitchenId == oldKitchenId) {
                    document.getElementById('editKodeMenu').value = oldKode;
                    return;
                }

                // Jika dapur berbeda → generate kode baru
                const kodeBaru = generatedCodes[selectedKitchenId] || "";
                document.getElementById('editKodeMenu').value = kodeBaru;
            });
        });
    </script>
@endpush
