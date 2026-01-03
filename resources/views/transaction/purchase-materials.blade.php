@extends('adminlte::page')

@section('title', 'Beli Bahan Baku')

@section('content_header')
    <h1>Pembelian Bahan Baku</h1>
@endsection

@section('content')
    <x-button-add idTarget="#modalAddPurchaseMaterials" text="Tambah Pembelian" />

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal Beli</th>
                        <th>Supplier</th>
                        <th>Total Harga</th>
                        <th width="230">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->kode }}</td>
                            <td>{{ $purchase->created_at }}</td>
                            <td>{{ $purchase->supplier->nama }}</td>
                            <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                            <td>

                                <button type="button" class="btn btn-primary btn-sm btn-detail" data-toggle="modal"
                                    data-id="{{ $purchase->id }}" data-target="#modalDetailPurchase">
                                    Detail
                                </button>
                                {{-- <button type="button" class="btn btn-sm btn-success btnEditPurchaseMaterials"
                                    data-toggle="modal" data-target="#modalEditPurchaseMaterials">
                                    Edit
                                </button> --}}
                                {{-- Kode BARU --}}
                                {{-- <a href="{{ route('transaction.purchase-materials.invoice', $purchase->id) }}"
                                    target="_blank" class="btn btn-sm btn-warning">
                                    <i class="fas fa-print mr-2"></i>Cetak Invoice
                                </a> --}}


                                <button type="button" class="btn btn-danger btn-sm"
                                    data-delete-target="#modalDeletePurchaseMaterials"
                                    data-action="{{ route('transaction.purchase-materials.destroy', $purchase->id) }}"
                                    data-form-id="formDeletePurchaseMaterials">
                                    Hapus
                                </button>


                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <x-modal-form id="modalAddPurchaseMaterials" size="modal-lg" title="Tambah Pembelian Bahan Baku"
        action="{{ route('transaction.purchase-materials.store') }}" submitText="Simpan" method="POST">
        @csrf

        <div class="d-flex align-items-center">
            <div class="form-group">
                <label>Kode</label>
                <input id="kode_transaksi_beli" type="text" class="form-control" name="kode"
                    value="{{ $kode }}" readonly required />

            </div>

            <div class="form-group flex-fill ml-2">
                <label>Tanggal Beli</label>
                <input type="date" class="form-control" name="tanggal" required />
            </div>

            <div class="form-group flex-fill ml-2">
                <label>Supplier</label>
                <select class="form-control" name="supplier" required>
                    <option value="" disabled selected>Pilih Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                    @endforeach
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
                            @foreach ($bahanBaku as $bahan)
                                <option value="{{ $bahan->id }}" data-harga="{{ $bahan->harga }}"
                                    data-satuan-id="{{ $bahan->satuan_id }}"
                                    data-satuan-nama="{{ $bahan->unit->satuan ?? '' }}">
                                    {{ $bahan->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="80" required />
                    </div>

                    <div class="col-md-3">
                        <select name="unit[]" class="form-control" required>
                            <option value="" disabled selected>Pilih Satuan</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="number" name="harga[]" class="form-control" required />
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-bahan d-none h-100"
                            style="width: 35px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-bahan" class="btn btn-outline-primary btn-block mt-2">
                <i class="fas fa-plus mr-1"></i>Tambah Pembelian
            </button>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT PURCHASE MATERIALS --}}
    <x-modal-form id="modalEditPurchaseMaterials" size="modal-lg" title="Beli Bahan Baku" action="" submitText="Beli">
        @method('PUT')

    </x-modal-form>

    {{-- MODAL DETAIL PURCHASE MATERIALS --}}
    <x-modal-detail id="modalDetailPurchase" size="modal-lg" title="Pemesanan Bahan Baku">

        <div>
            <p><strong>Kode: </strong><span id="detail-kode"></span></p>
            <p><strong>Tanggal Beli: </strong><span id="detail-tanggal"></span></p>
            <p><strong>Supplier: </strong><span id="detail-supplier"></span></p>
            <p><strong>Total Harga: </strong><span id="detail-total"></span></p>
        </div>
        <div>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Bahan Baku</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detail-items">
                </tbody>
            </table>
        </div>
        <div class="text-center mb-3">
            <a href="#" id="btn-print-invoice" target="_blank" class="btn btn-warning">
                <i class="fas fa-print mr-1"></i>Cetak Invoice
            </a>
        </div>
    </x-modal-detail>

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeletePurchaseMaterials" formId="formDeletePurchaseMaterials" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('bahan-wrapper');
            const addBtn = document.getElementById('add-bahan');

            addBtn.addEventListener('click', function() {
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
                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                });

                // Tambahkan row baru
                wrapper.appendChild(newRow);
            });

            // Event hapus untuk row pertama (opsional)
            const firstRemoveBtn = wrapper.querySelector('.remove-bahan');
            if (firstRemoveBtn) {
                firstRemoveBtn.addEventListener('click', function() {
                    firstRemoveBtn.closest('.bahan-group').remove();
                });
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target.name === 'bahan[]') {
                const selected = e.target.selectedOptions[0];
                const group = e.target.closest('.bahan-group');

                // ===== HARGA =====
                const harga = selected.dataset.harga;
                const hargaInput = group.querySelector('input[name="harga[]"]');
                if (harga && hargaInput) {
                    hargaInput.value = harga;
                }

                // ===== SATUAN =====
                const satuanId = selected.dataset.satuanId;
                const satuanNama = selected.dataset.satuanNama;
                const satuanSelect = group.querySelector('select[name="unit[]"]');

                if (satuanSelect && satuanId) {
                    satuanSelect.innerHTML = `
                    <option value="${satuanId}" selected>${satuanNama}</option>
                `;
                }
            }
        });

        document.addEventListener('click', function(e) {
            // Pastikan yang diklik adalah tombol detail
            if (e.target.classList.contains('btn-detail') || e.target.closest('.btn-detail')) {
                const btn = e.target.classList.contains('btn-detail') ? e.target : e.target.closest('.btn-detail');
                const purchaseId = btn.dataset.id;

                // 1. Atur Link Cetak Invoice di dalam Modal
                const printBtn = document.querySelector('#modalDetailPurchase #btn-print-invoice');
                if (printBtn) {
                    let url = "{{ route('transaction.purchase-materials.invoice', ':id') }}";
                    printBtn.href = url.replace(':id', purchaseId);
                    printBtn.setAttribute('download', '');
                }

                // 2. Ambil Data Detail via Fetch
                fetch(`/dashboard/transaksi/pembelian-bahan-baku/${purchaseId}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        // Isi Elemen Header Modal
                        document.getElementById('detail-kode').innerText = data.kode || '-';
                        document.getElementById('detail-tanggal').innerText = data.created_at ?
                            new Date(data.created_at).toLocaleDateString('id-ID') : '-';
                        document.getElementById('detail-supplier').innerText = data.supplier ? data.supplier
                            .nama : '-';

                        document.getElementById('detail-total').innerText = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(data.total || 0);

                        // Isi Tabel Items
                        const tbody = document.getElementById('detail-items');
                        tbody.innerHTML = '';

                        if (data.items && data.items.length > 0) {
                            data.items.forEach(item => {
                                tbody.innerHTML += `
                                        <tr>
                                            <td>${item.bahan_baku ? item.bahan_baku.nama : '-'}</td>
                                            <td>${item.jumlah}</td>
                                            <td>${item.bahan_baku && item.bahan_baku.unit ? item.bahan_baku.unit.satuan : '-'}</td>
                                            <td>Rp ${Number(item.harga).toLocaleString('id-ID')}</td>
                                            <td>Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                                        </tr>
                                    `;
                            });
                        } else {
                            tbody.innerHTML =
                                '<tr><td colspan="5" class="text-center">Tidak ada data item</td></tr>';
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('Gagal mengambil data detail: ' + err.message);
                    });
            }
        });

        document.addEventListener('click', function(e) {
            // Cari apakah yang diklik adalah tombol delete (atau elemen di dalamnya)
            const deleteBtn = e.target.closest('[data-target="#modalDeletePurchaseMaterials"]');

            if (deleteBtn) {
                // Ambil URL action dari atribut 'data-action' atau 'action' tombol yang diklik
                // Tergantung bagaimana komponen x-button-delete Anda merendernya
                const actionUrl = deleteBtn.getAttribute('data-action') || deleteBtn.closest('form')?.getAttribute(
                    'action');

                if (actionUrl) {
                    // Set action pada form yang ada di dalam modal delete
                    const deleteForm = document.getElementById('formDeletePurchaseMaterials');
                    if (deleteForm) {
                        deleteForm.setAttribute('action', actionUrl);
                    }
                }
            }
        });
    </script>
@endpush
