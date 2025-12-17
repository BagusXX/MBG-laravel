@extends('adminlte::page')

@section('title', 'Penjualan Bahan Baku')

@section('content_header')
    <h1>Penjualan Bahan Baku</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddSalesMaterials"
        text="Tambah Transaksi Penjualan Bahan Baku"
    />

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal Jual</th>
                        <th>Dapur</th>
                        {{-- <th>Bahan Baku</th> --}}
                        {{-- <th>Jumlah</th> --}}
                        {{-- <th>Satuan</th> --}}
                        {{-- <th>Harga</th> --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        {{-- <td></td> --}}
                        {{-- <td></td> --}}
                        {{-- <td></td> --}}
                        {{-- <td></td> --}}
                        <td>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                data-toggle="modal"
                                data-target="#modalDetailSales"
                            >
                                Detail
                            </button>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-warning btnEditSalesMaterials"
                                data-toggle="modal"
                                data-target="#modalEditSalesMaterials"
                            >
                                Edit
                            </button>
                            <x-button-delete
                                idTarget="#modalDeleteSalesMaterials"
                                formId="formDeleteSalesMaterials"
                                action="#"
                                text="Hapus"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <x-modal-form
        id="modalAddSalesMaterials"
        size="modal-lg"
        title="Tambah Transaksi Penjualan Bahan Baku"
        action="#"
        submitText="Simpan"
    >
        <div class="d-flex align-items-center">
            <div class="form-group">
                <label>Kode</label>
                <input 
                    id="kode_transaksi_beli"
                    type="text"
                    class="form-control"
                    name="kode"
                    readonly
                    required
                />
            </div>
    
            <div class="form-group flex-fill ml-2">
                <label>Tanggal Beli</label>
                <input type="date" class="form-control" name="tanggal" required />
            </div>

            <div class="form-group flex-fill ml-2">
                <label>Dapur</label>
                <select class="form-control" name="dapur" required>
                    <option value="" disabled selected>Pilih Dapur</option>
                    <option value=""></option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="form-row mb-2">
                <div class="col-md-3 font-weight-bold">Bahan</div>
                <div class="col-md-2 font-weight-bold">Jumlah</div>
                <div class="col-md-3 font-weight-bold">Satuan</div>
                <div class="col-md-3 font-weight-bold">Harga</div>
                <div class="col-md-1"></div>
            </div>

            <div id="bahan-wrapper">
                <div class="form-row mb-3 bahan-group">
                    <div class="col-md-3">
                        <select name="bahan[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Bahan</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="80" required />
                    </div>

                    <div class="col-md-3">
                        <select name="satuan[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Satuan</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <input type="number" name="harga[]" class="form-control" required />
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none h-100" style="width: 35px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-bahan" class="btn btn-outline-primary btn-block mt-2">
                <i class="fas fa-plus mr-1"></i>Tambah Penjualan
            </button>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT SALES MATERIALS --}}
    <x-modal-form
        id="modalEditSalesMaterials"
        size="modal-lg"
        title="Edit Transaksi Penjualan Bahan Baku"
        action=""
        submitText="Update"
    >
        @method('PUT')
        
    </x-modal-form>

    {{-- MODAL DETAIL --}}
    <x-modal-detail
        id="modalDetailSales"
        size="modal-lg"
        title="Detail Penjualan Bahan Baku"
    >
        <div>
            <div>
                <p class="font-weight-bold mb-0">Kode:</p>
                <p>BB202511111</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Tanggal Beli:</p>
                <p>Senin, 08 Desember 2025</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Dapur:</p>
                <p>Dapur A Tembalang</p>
            </div>
            <div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bawang Merah (Data Sampel)</td>
                            <td>500 kg</td>
                            <td>100000</td>
                        </tr>
                        <tr>
                            <td>Bawang Merah (Data Sampel)</td>
                            <td>500 kg</td>
                            <td>100000</td>
                        </tr>
                        <tr>
                            <td>Bawang Merah (Data Sampel)</td>
                            <td>500 kg</td>
                            <td>100000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal-detail>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteSalesMaterials"
        formId="formDeleteSalesMaterials"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
    />
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('bahan-wrapper');
            const addBtn = document.getElementById('add-bahan');

            addBtn.addEventListener('click', function () {
                const firstRow = wrapper.querySelector('.bahan-group');
                const newRow = firstRow.cloneNode(true);

                // Reset value input/select
                newRow.querySelectorAll('input, select').forEach(input => {
                    input.value = '';
                });

                // Tampilkan tombol hapus
                const removeBtn = newRow.querySelector('.remove-bahan');
                removeBtn.classList.remove('d-none');

                // Tambahkan event hapus
                removeBtn.addEventListener('click', function () {
                    newRow.remove();
                });

                // Tambahkan row baru
                wrapper.appendChild(newRow);
            });

            // Event hapus untuk row pertama (opsional)
            const firstRemoveBtn = wrapper.querySelector('.remove-bahan');
            if(firstRemoveBtn){
                firstRemoveBtn.addEventListener('click', function () {
                    firstRemoveBtn.closest('.bahan-group').remove();
                });
            }
        });
    </script>
@endpush