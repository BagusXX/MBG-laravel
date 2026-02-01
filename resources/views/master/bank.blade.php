@extends('adminlte::page')

@section('title', 'Akun Bank')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Akun Bank Supplier</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    {{-- @if($canManage) --}}
    <x-button-add 
        idTarget="#modalAddBank" 
        text="Tambah Akun Bank" 
    />
    {{-- @endif --}}

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50px">No</th>
                        <th>Nama Bank</th>
                        <th>Nasabah</th>
                        <th>No. Rekening</th>
                        @if($canManage)
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $index => $bank)
                        <tr>
                            <td>{{ $banks->firstItem() + $index }}</td>
                            <td>{{ $bank->nama_bank }}</td>
                            <td>{{ $bank->nasabah }}</td>
                            <td>{{ $bank->no_rekening }}</td>
                            @if($canManage)
                            <td>
                                <button 
                                    type="button"
                                    class="btn btn-warning btn-sm btnEditBank"
                                    data-id="{{ $bank->id }}"
                                    data-nama_bank="{{ $bank->nama_bank }}"
                                    data-nasabah="{{ $bank->nasabah }}"
                                    data-no_rekening="{{ $bank->no_rekening }}"
                                    data-toggle="modal"
                                    data-target="#modalEditBank"
                                >
                                    Edit
                                </button>
                                <x-button-delete 
                                    idTarget="#modalDeleteBank"
                                    formId="formDeleteBank"
                                    action="{{ route('master.bank.delete', $bank->id) }}"
                                    text="Hapus" 
                                />
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? '4' : '3' }}" class="text-center">Belum ada data akun bank</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $banks->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD AKUN BANK --}}
    @if($canManage)
    <x-modal-form
        id="modalAddBank"
        title="Tambah Akun Bank"
        action="{{ route('master.bank.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Bank</label>
            <input type="text" placeholder="Nama Bank" class="form-control" name="nama_bank" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Nasabah</label>
            <input type="text" placeholder="Nama Nasabah" class="form-control" name="nasabah" />
        </div>
        <div class="form-group mt-2">
            <label>No. Rekening</label>
            <input type="text" placeholder="No. Rekening" class="form-control" name="no_rekening" />
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form
        id="modalEditBank"
        title="Edit Akun Bank"
        action=""
        submitText="Update"
    >
        @method('PUT')
        
        <div class="form-group">
            <label>Nama Bank</label>
            <input id="editNamaBank" type="text" placeholder="Nama Bank" class="form-control" name="nama_bank" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Nasabah</label>
            <input id="editNasabah" type="text" placeholder="Nama Nasabah" class="form-control" name="nasabah" />
        </div>

        <div class="form-group mt-2">
            <label>No. Rekening</label>
            <input id="editNoRekening" type="text" placeholder="No. Rekening" class="form-control" name="no_rekening" />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteBank" 
        formId="formDeleteBank" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />
    @endif

@endsection

@section('js')
    @if($canManage)
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.btnEditBank').forEach(btn => {
                btn.addEventListener('click', function () {

                    const id = this.dataset.id;

                    // Isi field modal edit
                    document.getElementById('editNamaBank').value = this.dataset.nama_bank;
                    document.getElementById('editNasabah').value = this.dataset.nasabah;
                    document.getElementById('editNoRekening').value = this.dataset.no_rekening;

                    // Set action form update
                    document.querySelector('#modalEditBank form').action =
                        "{{ url('/dashboard/master/bank') }}/" + id;
                });
            });

        });
    </script>
    @endif
@endsection
