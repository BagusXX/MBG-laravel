<x-guest-layout>
    <div class="w-[480px] p-2 flex justify-center items-center bg-white rounded-lg">
        <div class="w-full px-8 py-6 flex justify-center items-center">
            <div class="w-full">
                <div class="mb-9 space-y-3 text-center">
                    <h1 class="font-semibold text-3xl text-black">Login</h1>
                    <p class="font-normal text-sm text-gray-400/70">
                        Masukkan email dan password untuk masuk ke akun Anda
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="mb-6 space-y-6" novalidate>
                    @csrf
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="email" text="Email" required />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" autofocus />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="relative flex flex-col gap-1">
                        <x-input-label id="password" text="Password" required/>
                        <x-text-input id="password" type="password" name="password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="flex justify-between items-center">
                        <div x-data="{ checked: false }">
                            <label for="remember" class="flex items-center gap-2 cursor-pointer">
                                <div 
                                    class="flex justify-center items-center w-4 h-4 border rounded-[3.5px] transition-all"
                                    :class="checked ? 'bg-blue-500 border-transparent' : 'border-gray-400/70'"
                                >
                                    <template x-if="checked">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="text-white" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </template>
                                </div>

                                <input 
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    x-model="checked"
                                    class="hidden"
                                />
                                <span 
                                    class="font-normal text-sm" 
                                    :class="checked ? 'text-black' : 'text-gray-400/70'"
                                >
                                    Remember me
                                </span>
                            </label>
                        </div>
                        
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" 
                               class="font-normal text-sm text-blue-500 hover:underline focus:outline-none focus:underline">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="pt-1">
                        <button
                            type="submit"
                            class="py-2 w-full bg-blue-500 font-normal text-base text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:bg-blue-600 active:bg-blue-700"
                        >
                            Login
                        </button>
                    </div>
                </form>
                <p class="font-normal text-sm text-center text-black">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-500 cursor-pointer hover:underline focus:outline-none focus:underline">Daftar disini</a></p>
            </div>
        </div>
    </div>
</x-guest-layout>
