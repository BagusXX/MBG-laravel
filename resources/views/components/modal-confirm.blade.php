<div class="modal fade" id="confirmActionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-xl">
            <div class="modal-header border-0">
                <button type="button" class="close position-absolute"
                        data-dismiss="modal"
                        style="top:10px; right:15px;">
                    <span style="font-size:2rem;">&times;</span>
                </button>
            </div>

            <div class="modal-body text-center px-4 py-4">
                <div id="confirmIconWrapper" class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:100px;height:100px;border:4px solid #ffc107;border-radius:50%;">
                    <span id="confirmIcon" style="font-size: 4rem; color: #ffc107;">!</span>
                </div>

                <h4 class="font-weight-bold" id="confirmModalTitle">
                    Konfirmasi
                </h4>

                <p class="text-secondary mt-2" id="confirmModalMessage">
                    Apakah Anda yakin?
                </p>

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button"
                            class="btn btn-secondary px-4 mr-2"
                            data-dismiss="modal">
                        Batal
                    </button>

                    <button type="button"
                            class="btn btn-warning px-4"
                            id="confirmModalSubmit">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        window.confirmAction = function ({
            title = 'Konfirmasi',
            message = 'Apakah Anda yakin?',
            confirmText = 'Ya, Lanjutkan',
            type= 'default',
            onConfirm = () => {}
        }) {
            const isDelete = type === 'delete';
            const isSuccess = type === 'success';

            // ===== Title & Message =====
            $('#confirmModalTitle')
                .text(title);

            $('#confirmModalMessage')
                .text(message);

            let iconHtml = '!';
            let color = '#ffc107';
            let btnClass = 'btn-warning';

            if (isDelete) {
                iconHtml = '&times;';
                color    = '#dc3545';
                btnClass = 'btn-danger';
            }

            if (isSuccess) {
                iconHtml = '<i class="fas fa-check" style="-webkit-text-stroke:1px transparent;font-size:3.5rem;"></i>';
                color    = '#28a745';
                btnClass = 'btn-success';
            }
            // ===== Icon =====
            $('#confirmIcon')
                .html(iconHtml)
                .css('color', color);

            // ===== Icon Border =====
            $('#confirmIconWrapper')
                .css('border-color', color);

            // ===== Button =====
            $('#confirmModalSubmit')
                .text(confirmText)
                .removeClass('btn-warning btn-danger btn-success')
                .addClass(btnClass);

            // ===== Click handler =====
            $('#confirmModalSubmit')
                .off('click')
                .on('click', function () {
                    $('#confirmActionModal').modal('hide');
                    onConfirm();
                });
                
            $('#confirmActionModal').modal({
                backdrop: false,
                keyboard: false,
                show: true
            });
        };
    </script>
@endpush