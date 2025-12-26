@extends('adminlte::page')

@section('title', 'Pengajuan Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Pengajuan Menu</h1>
@endsection

@section('content')

    <x-button-add idTarget="#modalAddSubmission" text="Tambah Pengajuan Menu" />

    <x-notification-pop-up />

    <div class="card">
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
                        <th width="220">Aksi</th>
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
                                        <span class="badge badge-{{ 
                                                            $item->status === 'diterima' ? 'success' :
                        ($item->status === 'ditolak' ? 'danger' :
                            ($item->status === 'diproses' ? 'info' : 'warning'))
                                                        }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- DETAIL --}}
                                        <a href="{{ route('transaction.submission.detail', $item->id) }}" class="btn btn-info btn-sm">
                                            Detail
                                        </a>

                                        {{-- EDIT --}}
                                        @if($item->status === 'diajukan')
                                            <button class="btn btn-warning btn-sm btnEdit"
                                                data-action="{{ route('transaction.submission.update', $item->id) }}"
                                                data-porsi="{{ $item->porsi }}" data-status="{{ $item->status }}" data-toggle="modal"
                                                data-target="#modalEditSubmission">
                                                Edit
                                            </button>
                                        @endif


                                        {{-- DELETE --}}
                                        @if($item->status === 'ditolak')
                                            <x-button-delete idTarget="#modalDeleteSubmission" formId="formDeleteSubmission"
                                                action="{{ route('transaction.submission.destroy', $item->id) }}" text="Hapus" />
                                        @endif
                                    </td>
                                </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-form id="modalAddSubmission" title="Tambah Pengajuan Menu"
    action="{{ route('transaction.submission.store') }}"
    submitText="Simpan">

    {{-- KODE --}}
    <div class="form-group">
    <label>Kode</label>
    <input
        type="text"
        name="kode"
        class="form-control"
        value="{{ $kode }}"
        readonly
    >
</div>


    {{-- TANGGAL --}}
    <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}">

    {{-- DAPUR --}}
    <div class="form-group">
        <label>Dapur</label>
        <select name="kitchen_id" id="kitchen_id" class="form-control" required>
            <option value="" disabled selected>Pilih Dapur</option>
            @foreach ($kitchens as $kitchen)
                <option value="{{ $kitchen->id }}">{{ $kitchen->nama }}</option>
            @endforeach
        </select>
    </div>

    {{-- MENU --}}
    <div class="form-group">
        <label>Menu</label>
        <select name="menu_id" id="menu_id" class="form-control" required>
            <option value="" disabled selected>Pilih dapur terlebih dahulu</option>
        </select>
    </div>

    {{-- PORSI --}}
    <div class="form-group">
        <label>Porsi</label>
        <input type="number" name="porsi" class="form-control" min="1" required>
    </div>

</x-modal-form>



    {{-- MODAL EDIT --}}
    <x-modal-form id="modalEditSubmission" title="Edit Pengajuan Menu" action="" submitText="Update">
        @method('PUT')

        <div class="form-group">
            <label>Porsi</label>
            <input type="number" id="edit_porsi" name="porsi" class="form-control">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select id="edit_status" name="status" class="form-control">
                <option value="diajukan">Diajukan</option>
                <option value="diproses">Diproses</option>
                <option value="diterima">Diterima</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
    </x-modal-form>

    <x-modal-delete id="modalDeleteSubmission" formId="formDeleteSubmission" title="Hapus Submission"
        message="Yakin ingin menghapus submission ini?" confirmText="Hapus" />

@endsection

@push('js')
<script>
    // =============================
    // EDIT MODAL
    // =============================
    $(document).on('click', '.btnEdit', function () {
        $('#modalEditSubmission form').attr('action', $(this).data('action'));
        $('#edit_porsi').val($(this).data('porsi'));
        $('#edit_status').val($(this).data('status'));
    });

    // =============================
    // LOAD MENU BY DAPUR (INI YANG KURANG)
    // =============================
    $(document).on('change', '#kitchen_id', function () {
        let kitchenId = $(this).val();
        let menuSelect = $('#menu_id');

        menuSelect.html('<option>Loading...</option>');

        fetch(`/dashboard/transaksi/pengajuan-menu/menu-by-kitchen/${kitchenId}`)
            .then(response => response.json())
            .then(data => {
                menuSelect.empty();
                menuSelect.append('<option disabled selected>Pilih Menu</option>');

                if (data.length === 0) {
                    menuSelect.append('<option disabled>Tidak ada menu</option>');
                }

                data.forEach(menu => {
                    menuSelect.append(
                        `<option value="${menu.id}">${menu.nama}</option>`
                    );
                });
            })
            .catch(error => {
                console.error(error);
                menuSelect.html('<option>Error load menu</option>');
            });
    });
</script>
@endpush
