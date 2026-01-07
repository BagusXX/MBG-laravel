@extends('adminlte::page')

@section('title', 'Satuan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Satuan</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddUnit" 
        text="Tambah Satuan" 
    />

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Satuan</th>
                        <th>Satuan Dasar</th> 
                        <th>Nilai Konversi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $index => $unit)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $unit->satuan }}</td>
                            <td>{{ $unit->base_unit }}</td>
                            <td>
    {{ rtrim(rtrim(number_format($unit->multiplier, 4, ',', '.'), '0'), ',') }}
</td>
                            <td>{{ $unit->keterangan ?? '-' }}</td>
                            <td>
                                <button 
                                    type="button"
                                    class="btn btn-warning btn-sm btnEditUnit"
                                    data-id="{{ $unit->id }}"
                                    data-satuan="{{ $unit->satuan }}"
                                    data-base_unit="{{ $unit->base_unit }}"
                                    data-multiplier="{{ $unit->multiplier }}"
                                    data-keterangan="{{ $unit->keterangan }}"
                                    data-toggle="modal"
                                    data-target="#modalEditUnit"
                                >
                                    Edit
                                </button>
                                <x-button-delete 
                                    idTarget="#modalDeleteUnit"
                                    formId="formDeleteUnit"
                                    action="{{ route('master.unit.destroy', $unit->id) }}"
                                    text="Hapus" 
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data satuan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD SATUAN --}}
    <x-modal-form
        id="modalAddUnit"
        title="Tambah Satuan"
        action="{{ route('master.unit.store') }}"
        submitText="Simpan"
    >
        {{-- Input Satuan (Misal: Kg, Lusin, Liter) --}}
    <div class="form-group">
        <label>Nama Satuan</label>
        <input type="text" placeholder="Contoh: Kg, Lusin, Liter" class="form-control" name="satuan" required />
    </div>

    {{-- UBAH BAGIAN INI: Base Unit jadi "Dikonversi ke Satuan Dasar" --}}
            <div class="form-group mt-2">
                <label>Dikonversi ke Satuan</label>
                <input type="text" placeholder="Contoh: gram, pcs, ml" class="form-control" name="base_unit" required />
                <small class="text-muted">
                    Satuan terkecil yang digunakan saat masak (Resep). <br>
                    <i>Contoh: Jika satuan beli 'Kg', maka satuan dasar biasanya 'gram'.</i>
                </small>
            </div>

            {{-- UBAH BAGIAN INI: Multiplier jadi "Jumlah Konversi" --}}
            <div class="form-group mt-2">
                <label>Jumlah Isi / Nilai Konversi</label>
                <input type="number" step="any" placeholder="Contoh: 1000" class="form-control" name="multiplier" required />
                <small class="text-muted">
                    1 Satuan di atas setara dengan berapa Satuan Dasar? <br>
                    <i>Contoh: 1 Kg = 1000 gram (Isi angka 1000).</i>
                </small>
            </div>
        
        <div class="form-group mt-2">
            <label>Keterangan (Opsional)</label>
            <input type="text" placeholder="Kilogram" class="form-control" name="keterangan" />
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form
        id="modalEditUnit"
        title="Edit Satuan"
        action=""
        submitText="Update"
    >
        @method('PUT')
        
        <div class="form-group">
        <label>Nama Satuan</label>
        <input id="editSatuan" type="text" class="form-control" name="satuan" required />
    </div>

    <div class="form-group mt-2">
        <label>Dikonversi ke Satuan Dasar</label>
        <input id="editBaseUnit" type="text" class="form-control" name="base_unit" required />
    </div>

    <div class="form-group mt-2">
        <label>Jumlah Isi / Nilai Konversi</label>
        <input id="editMultiplier" type="number" step="any" class="form-control" name="multiplier" required />
        <small class="text-muted">Contoh: Isi 12 jika 1 Lusin = 12 Pcs</small>
    </div>
        
        <div class="form-group mt-2">
            <label>Keterangan (Opsional)</label>
            <input id="editKeterangan" type="text" placeholder="Kilogram" class="form-control" name="keterangan" />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteUnit" 
        formId="formDeleteUnit" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.btnEditUnit').forEach(btn => {
                btn.addEventListener('click', function () {

                    const id = this.dataset.id;

                    // Isi field modal edit
                    document.getElementById('editSatuan').value = this.dataset.satuan;
                    document.getElementById('editBaseUnit').value = this.dataset.base_unit;
                    document.getElementById('editMultiplier').value = this.dataset.multiplier;
                    document.getElementById('editKeterangan').value = this.dataset.keterangan;

                    // Set action form update
                    document.querySelector('#modalEditUnit form').action =
                        "{{ url('/dashboard/master/satuan') }}/" + id;
                });
            });

        });
    </script>
@endsection
