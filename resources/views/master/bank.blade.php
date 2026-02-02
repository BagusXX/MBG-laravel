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
                <input id="editAccountNumber" type="text" 
                    class="form-control @error('account_number') is-invalid @enderror" 
                    name="account_number" 
                    value="{{ old('account_number') }}"
                    required />
                @error('account_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </x-modal-form>

        <x-modal-delete id="modalDeleteBank" formId="formDeleteBank" title="Konfirmasi Hapus"
            message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />
    @endif

@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('js')
    @if(isset($canManage) && $canManage)
        <script>
            
            document.addEventListener('DOMContentLoaded', function () {
                console.log("JS Loaded"); // Cek ini di Console F12

                // Fungsi Pengecekan
            async function isAccountDuplicate(accountNumber, currentId = null) {
                if (!accountNumber) return false;
                
                // Tambahkan URL param agar lebih jelas
                const url = `{{ route('master.bank.check') }}?account_number=${accountNumber}${currentId ? '&id=' + currentId : ''}`;
                
                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        // Jika masih error 500, kita bisa baca pesan errornya di console
                        const errData = await response.json();
                        console.error("Server Error Detail:", errData);
                        return false;
                    }

                    const data = await response.json();
                    return data.exists;
                } catch (e) {
                    console.error("Fetch error", e);
                    return false;
                }
            }

                // 1. PENANGANAN MODAL ADD
                const modalAdd = document.getElementById('modalAddBank');
                if (modalAdd) {
                    const formAdd = modalAdd.querySelector('form');
                    const inputAdd = modalAdd.querySelector('input[name="account_number"]');

                    formAdd.addEventListener('submit', async function (e) {
                        e.preventDefault(); // STOP FORM REFRESH
                        console.log("Checking Add Account...");

                        const duplicate = await isAccountDuplicate(inputAdd.value);
                        if (duplicate) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Nomor rekening ini sudah terdaftar!',
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            this.submit(); // Jalankan submit jika aman
                        }
                    });
                }

                // 2. PENANGANAN MODAL EDIT
                const modalEdit = document.getElementById('modalEditBank');
                let currentEditId = null;

                if (modalEdit) {
                    const formEdit = modalEdit.querySelector('form');
                    const inputEdit = document.getElementById('editAccountNumber');

                    // Listener untuk tombol edit di tabel
                    document.querySelectorAll('.btnEditBank').forEach(btn => {
                        btn.addEventListener('click', function () {
                            currentEditId = this.dataset.id;
                            document.getElementById('editSuppliersId').value = this.dataset.suppliers_id;
                            document.getElementById('editBankName').value = this.dataset.bank_name;
                            document.getElementById('editHolderName').value = this.dataset.account_holder_name;
                            inputEdit.value = this.dataset.account_number;
                            
                            formEdit.action = "{{ url('/dashboard/master/bank') }}/" + currentEditId;
                        });
                    });

                    formEdit.addEventListener('submit', async function (e) {
                        e.preventDefault(); // STOP FORM REFRESH
                        console.log("Checking Edit Account...");

                        const duplicate = await isAccountDuplicate(inputEdit.value, currentEditId);
                        if (duplicate) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Nomor Rekening Sama',
                                text: 'Nomor rekening sudah digunakan oleh supplier lain.',
                                confirmButtonColor: '#d33'
                            });
                        } else {
                            this.submit();
                        }
                    });
                }
            });
        </script>
    @endif
@endsection