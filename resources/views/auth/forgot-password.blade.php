<x-guest-layout>
    @if (session('status'))
        <div 
            id="notification" 
            class="absolute top-3 left-1/2 -translate-x-1/2 py-2 pr-[68px] pl-3 flex items-start gap-2 bg-white ring-1 ring-green-500 shadow-[0_2px_10px_0_rgba(0,0,0,0.3)] rounded-lg -translate-y-36 transition-all duration-500 ease-out"
        >
            <i class="fas fa-info-circle text-info text-lg text-green-500"></i>
            <div class="pt-0.5 space-y-0">
                <p class="font-normal text-base text-green-500">Berhasil!</p>
                <p class="max-w-96 font-normal text-base text-black">{{ session('status') }}</p>
            </div>
            <button 
                type="button"
                id="closeNotification"
                class="absolute top-[5px] right-3 text-2xl leading-none text-gray-500 hover:text-gray-700"
            >
                &times;
            </button>
        </div>
    @endif

    <div class="w-[480px] p-2 flex justify-center items-center bg-white rounded-lg">
        <div class="flex justify-center items-center px-8 py-6">
            <div>
                <div class="mb-9 space-y-3 text-center">
                    <h1 class="font-semibold text-3xl text-black">Lupa Password</h1>
                    <p class="font-normal text-sm text-gray-400/70">
                        Lupa password? Tidak masalah. Izinkan kami mengetahui email Anda dan kami akan mengirimi pesan email kepada Anda untuk melakukan reset password.
                    </p>
                </div>
                
                <form method="POST" action="{{ route('password.email') }}" class="space-y-6" novalidate>
                    @csrf
                    <!-- Email Address -->
                    <div class="relative flex flex-col gap-1 mb">
                        <x-input-label for="email" text="Email" required />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
            
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="py-2 w-full bg-blue-500 font-normal text-base text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:bg-blue-600 active:bg-blue-700"
                        >
                            Email Password Reset Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

@if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notification = document.getElementById('notification');
            const closeBtn = document.getElementById('closeNotification');

            if (!notification) return;

            // ===== Animasi Masuk =====
            requestAnimationFrame(() => {
                notification.classList.remove('-translate-y-36');
                notification.classList.add('translate-y-0');
            });

            // ===== Fungsi Tutup Notifikasi =====
            const closeNotification = () => {
                if (!notification.isConnected) return;

                notification.classList.remove('translate-y-0');
                notification.classList.add('-translate-y-36');

                setTimeout(() => {
                    notification.remove();
                }, 500);
            };

            // ===== Klik Tombol Close =====
            closeBtn.addEventListener('click', closeNotification);

            // ===== Auto Close + Pause Saat Hover =====
            let autoCloseTimer = setTimeout(closeNotification, 7000);

            notification.addEventListener('mouseenter', () => {
                clearTimeout(autoCloseTimer);
            });

            notification.addEventListener('mouseleave', () => {
                autoCloseTimer = setTimeout(closeNotification, 3000);
            });
        });
    </script>
@endif