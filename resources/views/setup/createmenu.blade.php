@extends('adminlte::page')

@section('title', 'Racik Menu')

@section('content_header')
    <h1>Racik Menu</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddRecipe"
        text="Racik Menu"   
    />

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Dapur</th>
                        <th>Nama Menu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Dapur A Tembalang</td>
                        <td>Nasi Goreng</td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm">Detail</button>
                            <button type="button" class="btn btn-warning btn-sm">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-form
        id="modalAddRecipe"
        title="Racik Menu"
        action="#"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Nama Dapur</label>
            <input type="text" placeholder="Dapur A Tembalang" class="form-control" name="dapur" required/>
        </div>
        <div class="form-group">
            <label>Nama Menu</label>
            <select type="text" class="form-control" name="dapur" required>
                <option value="" disabled selected>Pilih Nama Menu</option>
                <option value="nasi goreng">Nasi Goreng</option>
                <option value="mie ayam">Mie Ayam</option>
                <option value="rica-rica ayam">Rica-Rica Ayam</option>
            </select>
        </div>
        <div class="form-group">
            <div class="form-row mb-2">
                <div class="col-md-5 font-weight-bold">Bahan</div>
                <div class="col-md-2 font-weight-bold">Jumlah</div>
                <div class="col-md-4 font-weight-bold">Satuan</div>
                <div class="col-md-1"></div>
            </div>
            <div id="bahan-wrapper">
                <div class="form-row mb-3 bahan-group">
                    <div class="col-md-5">
                        <select name="bahan[]" class="form-control">
                            <option value="" disabled selected>Pilih Bahan</option>
                            <option value="bawang merah">Bawang</option>
                            <option value="bawang putih">Bawang Putih</option>
                            <option value="cabe merah">Cabe Merah</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="12">
                    </div>
                    <div class="col-md-4">
                        <select name="satuan[]" class="form-control">
                            <option value="" disabled selected>Pilih Satuan</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="L">Liter (L)</option>
                            <option value="mL">Mililiter (mL)</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end h-100">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none" style="height: 38px; width: 100%;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" id="add-bahan" class="btn btn-outline-primary btn-block">
                <i class="fas fa-plus mr-1"></i>Tambah Bahan
            </button>
        </div>
    </x-modal-form>
@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btnAdd = document.getElementById("add-bahan");
        const container = document.getElementById("bahan-wrapper");

        function updateRemoveButtons() {
            const rows = container.querySelectorAll(".bahan-group");
            const removeButtons = container.querySelectorAll(".remove-bahan");

            if (rows.length === 1) {
                removeButtons.forEach((btn) => btn.classList.add("d-none"));
            } else {
                removeButtons.forEach((btn) => btn.classList.remove("d-none"));
            }
        }

        btnAdd.addEventListener("click", () => {
            const row = document.createElement("div");
            row.classList.add("form-row", "mb-3", "bahan-group");

            row.innerHTML = `
                        <div class="col-md-5">
                            <select name="bahan[]" class="form-control">
                                <option value="" disabled selected>Pilih Bahan</option>
                                <option value="bawang merah">Bawang Merah</option>
                                <option value="bawang putih">Bawang Putih</option>
                                <option value="cabe merah">Cabe Merah</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="jumlah[]" class="form-control" placeholder="12">
                        </div>
                        <div class="col-md-4">
                            <select name="satuan[]" class="form-control">
                                <option value="" disabled selected>Pilih Satuan</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="L">Liter (L)</option>
                                <option value="mL">Mililiter (mL)</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end h-100">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none" style="height: 38px; width: 100%;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

            container.appendChild(row);

            updateRemoveButtons();
        });

        document.addEventListener("click", (e) => {
            const btn = e.target.closest(".remove-bahan");
            if (!btn) return;

            const row = btn.closest(".bahan-group");
            if (!row) return;

            row.remove();
            updateRemoveButtons();
        });

        updateRemoveButtons();
    });

</script>
@endpush
