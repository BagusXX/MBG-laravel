@if (session('success'))
    <div
        id="adminNotification"
        class="admin-toast shadow"
    >
        <i class="fas fa-info-circle text-success mr-2"></i>
        <div class="toast-body">
            <strong class="text-success d-block">
                {{ $title ?? 'Berhasil!' }}
            </strong>
            <p>{{ session('success') }}</p>
        </div>

        <button
            type="button"
            class="close ml-3"
            id="closeAdminNotification"
        >
            &times;
        </button>
    </div>

    @push('js')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('adminNotification');
            const closeBtn = document.getElementById('closeAdminNotification');

            if (!toast) return;

            // === Show animation ===
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            const closeToast = () => {
                if (!toast.isConnected) return;

                toast.classList.remove('show');
                toast.classList.add('hide');

                setTimeout(() => {
                    toast.remove();
                }, 500);
            };

            // Close button
            closeBtn.addEventListener('click', closeToast);

            // Auto close (7 detik)
            let autoCloseTimer = setTimeout(closeToast, 7000);

            // Pause on hover
            toast.addEventListener('mouseenter', () => {
                clearTimeout(autoCloseTimer);
            });

            toast.addEventListener('mouseleave', () => {
                autoCloseTimer = setTimeout(closeToast, 3000);
            });
        });
        </script>
    @endpush
@endif
