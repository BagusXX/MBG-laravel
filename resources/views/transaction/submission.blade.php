@extends('adminlte::page')

@section('title', 'Pengajuan Menu')

@section('content_header')
    <h1>Pengajuan Menu</h1>
@endsection

@section('content')

    {{-- TOMBOL TAMBAH --}}
    <x-button-add idTarget="#modalAddSubmission" text="Tambah Pengajuan Menu" />

    <x-notification-pop-up />

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Dapur</th>
                        <th>Menu</th>
                        <th>Porsi</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $item)
                        <tr>
                            <td>{{ $item->kode }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $item->kitchen->nama }}</td>
                            <td>{{ $item->menu->nama }}</td>
                            <td>{{ $item->porsi }}</td>
                            <td>
                                <span class="badge badge-{{ $item->status === 'selesai' ? 'success' : ($item->status === 'ditolak' ? 'danger' : ($item->status === 'diproses' ? 'info' : 'warning')) }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                            <td>
                                {{-- TOMBOL DETAIL --}}
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalViewDetail{{ $item->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- TOMBOL EDIT & HAPUS (Hanya jika status diajukan) --}}
                                @if($item->status === 'diajukan')
                                    <button type="button" class="btn btn-warning btn-sm btnEditSubmission"
                                        data-id="{{ $item->id }}"
                                        data-update-url="{{ route('transaction.submission.update', $item->id) }}"
                                        data-kode="{{ $item->kode }}"
                                        data-kitchen-id="{{ $item->kitchen_id }}"
                                        data-kitchen-nama="{{ $item->kitchen->nama }}"
                                        data-menu-id="{{ $item->menu_id }}"
                                        data-menu-nama="{{ $item->menu->nama }}"
                                        data-porsi="{{ $item->porsi }}"
                                        data-toggle="modal" data-target="#modalEditSubmission">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    <x-button-delete idTarget="#modalDeleteSubmission" formId="formDeleteSubmission"
                                        action="{{ route('transaction.submission.destroy', $item->id) }}" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data pengajuan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $submissions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <x-modal-form id="modalAddSubmission" title="Tambah Pengajuan Menu" action="{{ route('transaction.submission.store') }}" submitText="Simpan">
        <div class="form-group">
            <label>Kode</label>
            <input type="text" class="form-control" value="{{ $nextKode }}" readonly style="background:#e9ecef">
        </div>
        <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}">
        <div class="form-group">
            <label>Dapur</label>
            <select name="kitchen_id" id="kitchen_id" class="form-control" required>
                <option disabled selected>Pilih Dapur</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Menu</label>
            <select name="menu_id" id="menu_id" class="form-control" required>
                <option disabled selected>Pilih dapur terlebih dahulu</option>
            </select>
        </div>
        <div class="form-group">
            <label>Porsi</label>
            <input type="number" name="porsi" min="1" class="form-control" required>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT HEADER --}}
    <x-modal-form id="modalEditSubmission" title="Edit Pengajuan" action="" submitText="Perbarui">
        @method('PUT')
        <div class="form-group">
            <label>Kode</label>
            <input id="editKodePengajuan" type="text" class="form-control" name="kode" readonly />
        </div>
        <input id="editKitchenId" type="hidden" name="kitchen_id" />
        <div class="form-group">
            <label>Dapur</label>
            <input id="editKitchenNama" type="text" class="form-control" readonly />
        </div>
        <input id="editMenuId" type="hidden" name="menu_id" />
        <div class="form-group">
            <label>Menu</label>
            <input id="editMenuNama" type="text" class="form-control" readonly />
        </div>
        <div class="form-group">
            <label>Porsi</label>
            <input id="editPorsi" type="number" class="form-control" name="porsi" required />
            <small class="text-danger">* Mengubah porsi akan mereset detail bahan baku ke standar resep.</small>
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeleteSubmission" formId="formDeleteSubmission" title="Konfirmasi Hapus" message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />

    {{-- MODAL VIEW DETAIL --}}
    @foreach ($submissions as $item)
        <x-modal-detail id="modalViewDetail{{ $item->id }}" size="modal-lg" title="Detail Pengajuan">
            <table class="table table-borderless">
                <tr><th width="140">Kode</th><td>: {{ $item->kode }}</td></tr>
                <tr><th>Tanggal</th><td>: {{ date('d-m-Y', strtotime($item->tanggal)) }}</td></tr>
                <tr><th>Dapur</th><td>: {{ $item->kitchen->nama }}</td></tr>
                <tr><th>Menu</th><td>: {{ $item->menu->nama }}</td></tr>
                <tr><th>Porsi</th><td>: {{ $item->porsi }}</td></tr>
            </table>
            <table class="table table-bordered table-striped table-sm">
                <thead><tr><th>Bahan Baku</th><th>Qty</th><th>Satuan</th></tr></thead>
                <tbody>
                    @foreach ($item->details as $detail)
                        <tr>
                            <td>{{ $detail->recipe?->bahan_baku?->nama ?? $detail->bahanBaku?->nama ?? '-' }}</td>
                            <td>{{ number_format($detail->qty_digunakan, 2, ',', '.') }}</td>
                            <td>{{ $detail->recipe?->bahan_baku?->unit?->satuan ?? $detail->bahanBaku?->unit?->satuan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-modal-detail>
    @endforeach

@endsection

@push('js')
<script>
    $(document).ready(function() {
        // --- LOGIC MENU DROPDOWN (TAMBAH) ---
        $('#kitchen_id').change(function() {
            let kitchenId = $(this).val();
            $('#menu_id').html('<option disabled selected>Loading...</option>');
            let url = "{{ route('transaction.submission.menu', ':kitchen') }}".replace(':kitchen', kitchenId);
            
            $.get(url).done(function(data) {
                $('#menu_id').empty().append('<option disabled selected>Pilih Menu</option>');
                data.forEach(menu => $('#menu_id').append(`<option value="${menu.id}">${menu.nama}</option>`));
            });
        });

        // --- LOGIC MODAL EDIT ---
        $('.btnEditSubmission').click(function() {
            $('#modalEditSubmission form').attr('action', $(this).data('update-url'));
            $('#editKodePengajuan').val($(this).data('kode'));
            $('#editKitchenId').val($(this).data('kitchen-id'));
            $('#editKitchenNama').val($(this).data('kitchen-nama'));
            $('#editMenuId').val($(this).data('menu-id'));
            $('#editMenuNama').val($(this).data('menu-nama'));
            $('#editPorsi').val($(this).data('porsi'));
        });
    });
</script>
@endpush