@extends('adminlte::page')

@section('title', 'Penjualan Bahan Baku')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Penjualan Bahan Baku</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddSalesMaterials"
        text="Jual Bahan Baku"
    />

    <x-notification-pop-up />

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
                    @forelse($sales as $index => $sale)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $sale->kode ?? '-' }}</td>
                            <td>{{ $sale->tanggal ? \Carbon\Carbon::parse($sale->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $sale->kitchen ? $sale->kitchen->nama : '-' }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-toggle="modal"
                                    data-target="#modalDetailSales"
                                    data-id="{{ $sale->id }}"
                                    data-kode="{{ $sale->kode ?? '-' }}"
                                    data-tanggal="{{ $sale->tanggal ? \Carbon\Carbon::parse($sale->tanggal)->format('d F Y') : '-' }}"
                                    data-dapur="{{ $sale->kitchen ? $sale->kitchen->nama : '-' }}"
                                    data-bahan="{{ $sale->bahanBaku ? $sale->bahanBaku->nama : '-' }}"
                                    data-jumlah="{{ $sale->bobot_jumlah }}"
                                    data-satuan="{{ $sale->satuan ? $sale->satuan->satuan : ($sale->bahanBaku && $sale->bahanBaku->unit ? $sale->bahanBaku->unit->satuan : '-') }}"
                                    data-harga="{{ number_format($sale->harga, 0, ',', '.') }}"
                                >
                                    Detail
                                </button>
                                {{-- <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning btnEditSalesMaterials"
                                    data-toggle="modal"
                                    data-target="#modalEditSalesMaterials"
                                >
                                    Edit
                                </button> --}}
                                <x-button-delete
                                    idTarget="#modalDeleteSalesMaterials"
                                    formId="formDeleteSalesMaterials"
                                    action="#"
                                    text="Hapus"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data penjualan bahan baku</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <x-modal-form
        id="modalAddSalesMaterials"
        size="modal-lg"
        title="Tambah Penjualan Bahan Baku"
        action="{{ route('transaction.sale-materials-kitchen.store') }}"
        submitText="Simpan"
    >
        <div class="d-flex align-items-center">
            <div class="form-group">
                <label>Kode</label>
                <input 
                    id="kode_transaksi_beli"
                    type="text"
                    class="form-control"
                    value="{{ $nextKode }}"
                    readonly
                    style="background:#e9ecef"
                />
            </div>
    
            <div class="form-group flex-fill ml-2">
                <label>Tanggal Jual</label>
                <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required />
            </div>

            <div class="form-group flex-fill ml-2">
                <label>Dapur</label>
                <select id="kitchen_id" name="kitchen_id" class="form-control" required>
                    <option value="" disabled selected>Pilih Dapur</option>
                    @foreach($kitchens as $kitchen)
                        <option value="{{ $kitchen->id }}">{{ $kitchen->nama }}</option>
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
                        <select name="bahan_id[]" class="form-control bahan-select" required>
                            <option value="" disabled selected>Pilih Dapur terlebih dahulu</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="80" min="1" required />
                    </div>

                    <div class="col-md-3">
                        <select name="satuan_id[]" class="form-control satuan-select" required>
                            <option value="" disabled selected>Pilih Satuan</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->satuan }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <input type="number" name="harga[]" class="form-control harga-input" placeholder="0" min="0" step="0.01" required />
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
            <div class="mb-3">
                <p class="font-weight-bold mb-0">Kode:</p>
                <p id="detail-kode">-</p>
            </div>
            <div class="mb-3">
                <p class="font-weight-bold mb-0">Tanggal Jual:</p>
                <p id="detail-tanggal">-</p>
            </div>
            <div class="mb-3">
                <p class="font-weight-bold mb-0">Dapur:</p>
                <p id="detail-dapur">-</p>
            </div>
            <div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody id="detail-tbody">
                        <tr>
                            <td id="detail-bahan">-</td>
                            <td id="detail-jumlah">-</td>
                            <td id="detail-satuan">-</td>
                            <td id="detail-harga">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-center">
                <a 
                    id="detail-download-invoice"
                    href="#"
                    class="btn btn-warning"
                >
                    <i class="fas fa-download"></i> Download Invoice PDF
                </a>
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
        $(document).ready(function () {
            let bahanData = {}; // Menyimpan data bahan berdasarkan dapur

            /**
             * ======================================================
             * LOAD BAHAN BERDASARKAN DAPUR
             * ======================================================
             */
            function loadBahanByKitchen(kitchenId) {
                if (!kitchenId) {
                    // Reset semua dropdown bahan
                    $('.bahan-select').html('<option value="" disabled selected>Pilih Dapur terlebih dahulu</option>');
                    return;
                }

                // Generate URL route dengan parameter
                let url = "{{ route('transaction.sale-materials-kitchen.bahan-by-kitchen', ':kitchen') }}";
                url = url.replace(':kitchen', kitchenId);

                $.get(url)
                    .done(function (data) {
                        bahanData[kitchenId] = data;

                        // Update semua dropdown bahan
                        $('.bahan-select').each(function() {
                            let currentValue = $(this).val();
                            $(this).empty();
                            $(this).append('<option value="" disabled selected>Pilih Bahan</option>');

                            if (data.length === 0) {
                                $(this).append('<option disabled>Tidak ada bahan untuk dapur ini</option>');
                                return;
                            }

                            data.forEach(function (bahan) {
                                let selected = (bahan.id == currentValue) ? 'selected' : '';
                                $(this).append(
                                    `<option value="${bahan.id}" data-harga="${bahan.harga}" data-satuan-id="${bahan.satuan_id}" ${selected}>${bahan.nama}</option>`
                                );
                            }.bind(this));
                        });
                    })
                    .fail(function () {
                        $('.bahan-select').html('<option disabled selected>Gagal memuat bahan</option>');
                    });
            }

            /**
             * ======================================================
             * SAAT DAPUR DIPILIH
             * ======================================================
             */
            $(document).on('change', '#kitchen_id', function () {
                let kitchenId = $(this).val();

                // Reset semua bahan
                $('.bahan-select').html('<option value="" disabled selected>Pilih Bahan</option>');
                $('.harga-input').val('');
                $('.satuan-select').val('');

                if (kitchenId) {
                    loadBahanByKitchen(kitchenId);
                } else {
                    $('.bahan-select').html('<option value="" disabled selected>Pilih Dapur terlebih dahulu</option>');
                }
            });

            /**
             * ======================================================
             * SAAT BAHAN DIPILIH - AUTO FILL HARGA & SATUAN
             * ======================================================
             */
            $(document).on('change', '.bahan-select', function () {
                let selectedOption = $(this).find('option:selected');
                let harga = selectedOption.data('harga');
                let satuanId = selectedOption.data('satuan-id');
                let row = $(this).closest('.bahan-group');

                // Set harga
                if (harga) {
                    row.find('.harga-input').val(harga);
                }

                // Set satuan
                if (satuanId) {
                    row.find('.satuan-select').val(satuanId);
                }
            });

            /**
             * ======================================================
             * TAMBAH ROW BAHAN BARU
             * ======================================================
             */
            $('#add-bahan').on('click', function () {
                let wrapper = $('#bahan-wrapper');
                let firstRow = wrapper.find('.bahan-group').first();
                let newRow = firstRow.clone(true); // Clone dengan event handlers

                // Reset value input/select
                newRow.find('input').val('');
                newRow.find('.bahan-select').val('');
                newRow.find('.satuan-select').val('');
                newRow.find('.harga-input').val('');

                // Load bahan jika dapur sudah dipilih
                let kitchenId = $('#kitchen_id').val();
                if (kitchenId && bahanData[kitchenId]) {
                    newRow.find('.bahan-select').empty();
                    newRow.find('.bahan-select').append('<option value="" disabled selected>Pilih Bahan</option>');
                    bahanData[kitchenId].forEach(function (bahan) {
                        newRow.find('.bahan-select').append(
                            `<option value="${bahan.id}" data-harga="${bahan.harga}" data-satuan-id="${bahan.satuan_id}">${bahan.nama}</option>`
                        );
                    });
                } else {
                    newRow.find('.bahan-select').html('<option value="" disabled selected>Pilih Dapur terlebih dahulu</option>');
                }

                // Tampilkan tombol hapus
                newRow.find('.remove-bahan').removeClass('d-none');

                // Tambahkan event hapus
                newRow.find('.remove-bahan').off('click').on('click', function () {
                    newRow.remove();
                });

                // Tambahkan row baru
                wrapper.append(newRow);
            });

            /**
             * ======================================================
             * SAAT MODAL DIBUKA - LOAD BAHAN JIKA DAPUR SUDAH DIPILIH
             * ======================================================
             */
            $('#modalAddSalesMaterials').on('shown.bs.modal', function () {
                let kitchenId = $('#kitchen_id').val();
                if (kitchenId) {
                    loadBahanByKitchen(kitchenId);
                }
            });

            /**
             * ======================================================
             * SAAT MODAL DITUTUP → RESET FORM
             * ======================================================
             */
            $('#modalAddSalesMaterials').on('hidden.bs.modal', function () {
                $('#kitchen_id').val('');
                $('.bahan-select').html('<option value="" disabled selected>Pilih Dapur terlebih dahulu</option>');
                $('.harga-input').val('');
                $('.satuan-select').val('');

                // Reset ke 1 row saja
                let wrapper = $('#bahan-wrapper');
                let firstRow = wrapper.find('.bahan-group').first();
                wrapper.empty();
                wrapper.append(firstRow);
                firstRow.find('.remove-bahan').addClass('d-none');
            });

            /**
             * ======================================================
             * HANDLE MODAL DETAIL BUTTON CLICK
             * ======================================================
             */
            $(document).on('click', '[data-target="#modalDetailSales"]', function() {
                var kode = $(this).data('kode');
                $('#detail-kode').text(kode || '-');
                $('#detail-tanggal').text($(this).data('tanggal') || '-');
                $('#detail-dapur').text($(this).data('dapur') || '-');
                $('#detail-bahan').text($(this).data('bahan') || '-');
                $('#detail-jumlah').text($(this).data('jumlah') || '-');
                $('#detail-satuan').text($(this).data('satuan') || '-');
                $('#detail-harga').text('Rp ' + ($(this).data('harga') || '0'));
                
                // Set download invoice link
                if (kode && kode !== '-') {
                    var downloadUrl = "{{ route('transaction.sale-materials-kitchen.invoice.download', ':kode') }}";
                    downloadUrl = downloadUrl.replace(':kode', kode);
                    $('#detail-download-invoice').attr('href', downloadUrl).removeClass('disabled');
                } else {
                    $('#detail-download-invoice').attr('href', '#').addClass('disabled');
                }
            });
        });
    </script>
@endpush