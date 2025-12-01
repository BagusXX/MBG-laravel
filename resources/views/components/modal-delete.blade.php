<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-xl">
            <button type="button" class="close position-absolute" 
                    data-dismiss="modal" aria-label="Close"
                    style="top: 10px; right: 15px; z-index: 10;">
                <span aria-hidden="true" style="font-size: 2rem;">&times;</span>
            </button>
            <div class="modal-body d-flex flex-column align-items-center text-center px-4 py-4">
                <div class="d-flex justify-content-center align-items-center"
                     style="
                        width: 100px; 
                        height: 100px; 
                        border: 4px solid #dc3545;
                        border-radius: 50%;
                     ">
                    <span style="font-size: 4rem; color: #dc3545;">&times;</span>
                </div>
                <h3 class="font-weight-bold mt-3">{{ $title ?? 'Apakah Anda Yakin?' }}</h3>
                <p class="mt-2 text-secondary">
                    {{ $message ?? 'Apakah Anda yakin ingin menghapus data ini?' }}
                </p>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-secondary px-4 mr-3"
                            data-dismiss="modal">
                        Batal
                    </button>
                    <form method="POST" id="{{ $formId }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">
                            {{ $confirmText ?? 'Hapus' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deleteButtons = document.querySelectorAll('[data-delete-target]');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', () => {

                const modalId = btn.getAttribute('data-delete-target');
                const action = btn.getAttribute('data-action');
                const formId = btn.getAttribute('data-form-id');

                const form = document.getElementById(formId);
                if (form) {
                    form.setAttribute('action', action);
                }

                $(modalId).modal('show');
            });
        });
    });

</script>
@endpush