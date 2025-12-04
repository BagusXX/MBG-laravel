<x-guest-layout>
    <div class="p-2 flex justify-center items-center bg-white rounded-lg">
        <div class="px-8 py-6 flex justify-center items-center">
            <div class="space-y-10">
                <div class="space-y-3 text-center">
                    <h1 class="font-semibold text-3xl text-black">Login</h1>
                    <p class="font-normal text-sm text-gray-400/70">
                        Masukkan email dan password untuk masuk ke akun Anda
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label for="email" class="font-normal text-sm text-black">Email</label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="px-3 py-2 font-normal text-sm text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
                        >
                        @error('email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="password" class="font-normal text-sm text-black">Password</label>
                        <input 
                            id="password" 
                            type="password" 
                            name="password"
                            required
                            class="px-3 py-2 font-normal text-sm text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-400"
                        >
                        @error('password')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
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
                                :class="checked ? 'text-black' : 'text-gray-400/70'">
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
                    <div class="pt-3">
                        <button
                            type="submit"
                            class="mt-2 py-2 w-full bg-blue-500 font-normal text-sm text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:bg-blue-600 active:bg-blue-700"
                        >
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
