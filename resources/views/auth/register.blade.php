<x-guest-layout>
    <div class="w-[480px] p-2 flex justify-center items-center bg-white rounded-lg">
        <div class="flex justify-center items-center px-8 py-6">
            <div>
                <div class="mb-9 space-y-3 text-center">
                    <h1 class="font-semibold text-3xl text-black">Register</h1>
                    <p class="font-normal text-sm text-gray-400/70">
                        Masukkan data-data yang diperlukan untuk membuat akun Anda
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="mb-6 space-y-6" novalidate>
                    @csrf
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="name" text="Nama" required />
                        <x-text-input id="name" type="text" name="name" :value="old('name')" autofocus />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="email" text="Email" required />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="password" text="Password" required />
                        <x-text-input id="password" type="password" name="password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="password_confirmation" text="Konfirmasi Password" required />
                        <x-text-input id="password_confirmation" type="password" name="password_confirmation" />
                        <x-input-error :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="py-2 w-full bg-blue-500 font-normal text-base text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:bg-blue-600 active:bg-blue-700"
                        >
                            Register
                        </button>
                    </div>
                </form>
                <p class="font-normal text-sm text-center text-black">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-500 cursor-pointer hover:underline focus:outline-none focus:underline">Masuk disini</a></p>
            </div>
        </div>
    </div>
</x-guest-layout>
