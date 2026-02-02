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
    @if(isset($canManage) && $canManage)
        <x-button-add idTarget="#modalAddBank" text="Tambah Akun Bank" />
    @endif

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50px">No</th>
                        <th>Supplier</th>
                        <th>Nama Bank</th>
                        <th>Nasabah (Holder)</th>
                        <th>No. Rekening</th>
                        @if(isset($canManage) && $canManage)
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankAccounts as $index => $bank)
                        <tr>
                            <td>{{ $bankAccounts->firstItem() + $index }}</td>

                            <td>
                                {{ $bank->suppliers->nama ?? '-' }}
                                <br>
                                <small class="text-muted">{{ $bank->suppliers->kode ?? '' }}</small>
                            </td>

                            <td>{{ $bank->bank_name }}</td>
                            <td>{{ $bank->account_holder_name }}</td>
                            <td>{{ $bank->account_number }}</td>

                            @if(isset($canManage) && $canManage)
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm btnEditBank" data-id="{{ $bank->id }}"
                                        data-suppliers_id="{{ $bank->suppliers_id }}" data-bank_name="{{ $bank->bank_name }}"
                                        data-account_holder_name="{{ $bank->account_holder_name }}"
                                        data-account_number="{{ $bank->account_number }}" data-toggle="modal"
                                        data-target="#modalEditBank">
                                        Edit
                                    </button>

                                    {{-- PERUBAHAN DISINI: route('master.bank.delete') menjadi route('master.bank.destroy') --}}
                                    <x-button-delete idTarget="#modalDeleteBank" formId="formDeleteBank"
                                        action="{{ route('master.bank.destroy', $bank->id) }}" text="Hapus" />
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ (isset($canManage) && $canManage) ? '6' : '5' }}" class="text-center">Belum ada data
                                akun bank</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $bankAccounts->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if(isset($canManage) && $canManage)
        {{-- MODAL ADD AKUN BANK --}}
        {{-- Route store tetap sama: master.bank.store --}}
        <x-modal-form id="modalAddBank" title="Tambah Akun Bank" action="{{ route('master.bank.store') }}" submitText="Simpan">

            <div class="form-group">
                <label>Pilih Supplier</label>
                <select name="suppliers_id" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    @if(isset($suppliers))
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kode }})</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="form-group mt-2">
                <label>Nama Bank</label>
                <input type="text" placeholder="Contoh: BCA / Mandiri" class="form-control" name="bank_name" required />
            </div>

            <div class="form-group mt-2">
                <label>Nama Pemilik Rekening (Nasabah)</label>
                <input type="text" placeholder="Nama sesuai buku tabungan" class="form-control" name="account_holder_name"
                    required />
            </div>

            <div class="form-group mt-2">
                <label>No. Rekening</label>
                <input type="text" placeholder="Nomor Rekening" class="form-control" name="account_number" inputmode="numeric"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
            </div>
        </x-modal-form>

        {{-- MODAL EDIT --}}
        <x-modal-form id="modalEditBank" title="Edit Akun Bank" action="" submitText="Update">
            @method('PUT')

            <div class="form-group">
                <label>Supplier</label>
                <select id="editSuppliersId" name="suppliers_id" class="form-control" disabled>
                    @if(isset($suppliers))
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="form-group mt-2">
                <label>Nama Bank</label>
                <input id="editBankName" type="text" class="form-control" name="bank_name" required />
            </div>

            <div class="form-group mt-2">
                <label>Nama Pemilik Rekening</label>
                <input id="editHolderName" type="text" class="form-control" name="account_holder_name" required />
            </div>

            <div class="form-group mt-2">
                <label>No. Rekening</label>
                <input id="editAccountNumber" type="text" class="form-control" name="account_number" required />
            </div>
        </x-modal-form>

        <x-modal-delete id="modalDeleteBank" formId="formDeleteBank" title="Konfirmasi Hapus"
            message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />
    @endif

@endsection

@section('js')
    @if(isset($canManage) && $canManage)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->has('account_number'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan!',
                        text: '{{ $errors->first("account_number") }}', // Mengambil pesan custom dari controller
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Tutup'
                    }).then((result) => {
                        // Opsi Tambahan: Buka kembali modal input agar user bisa langsung ganti
                        // Sesuaikan ID modalnya (Tambah atau Edit)
                        // Logika sederhana: jika ada error, buka modal tambah (atau sesuaikan kebutuhan)
                        $('#modalAddBank').modal('show');
                    });
                @endif
                document.querySelectorAll('.btnEditBank').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const id = this.dataset.id;

                        // Ambil data dari atribut tombol
                        document.getElementById('editSuppliersId').value = this.dataset.suppliers_id;
                        document.getElementById('editBankName').value = this.dataset.bank_name;
                        document.getElementById('editHolderName').value = this.dataset.account_holder_name;
                        document.getElementById('editAccountNumber').value = this.dataset.account_number;

                        // Update action URL untuk form Edit
                        // Karena prefix URL tidak berubah (/dashboard/master/bank), JS ini tetap aman
                        document.querySelector('#modalEditBank form').action =
                            "{{ url('/dashboard/master/bank') }}/" + id;
                    });
                });

            });
        </script>
    @endif
@endsection