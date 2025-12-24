<x-guest-layout>
    <div class="w-[480px] p-2 flex justify-center items-center bg-white rounded-lg">
        <div class="flex justify-center items-center px-8 py-6 w-full">
            <div class="w-full">
                {{-- Header (Judul) --}}
                <div class="mb-9 space-y-3 text-center">
                    <h1 class="font-semibold text-3xl text-black">Register</h1>
                    <p class="font-normal text-sm text-gray-400/70">
                        Masukkan data-data yang diperlukan
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="mb-6 space-y-6" novalidate>
                    @csrf

                    {{-- ... (Input Nama, Email, Password biarkan sama) ... --}}
                    
                     {{-- NAMA --}}
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="name" text="Nama" required />
                        <x-text-input id="name" type="text" name="name" :value="old('name')" autofocus />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    {{-- EMAIL --}}
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="email" text="Email" required />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    {{-- PASSWORD --}}
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="password" text="Password" required />
                        <x-text-input id="password" type="password" name="password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    {{-- KONFIRMASI PASSWORD --}}
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="password_confirmation" text="Konfirmasi Password" required />
                        <x-text-input id="password_confirmation" type="password" name="password_confirmation" />
                        <x-input-error :messages="$errors->get('password_confirmation')" />
                    </div>

                    {{-- 1. PILIH REGION (FILTER) --}}
                    {{-- Tidak perlu attribute 'name' karena tidak disimpan ke DB User --}}
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="region_filter" text="Pilih Wilayah (Region)" />
                        
                        <select id="region_filter"
                            class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 w-full cursor-pointer bg-white">
                            
                            <option value="" selected>-- Pilih Wilayah Dahulu --</option>

                            @foreach ($regions as $region)
                                {{-- Value pakai ID region --}}
                                <option value="{{ $region->id }}">
                                    {{ $region->nama_region }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. PILIH DAPUR (HASIL FILTER) --}}
                    <div class="relative flex flex-col gap-1">
                        <x-input-label for="kitchens" text="Pilih Dapur" required />
                        
                        {{-- Disabled default, akan aktif jika region dipilih --}}
                        <select name="kitchens[]" id="kitchens" required disabled
                            class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 w-full cursor-pointer bg-gray-100">
                            
                            <option value="" disabled selected>-- Pilih Wilayah Diatas Terlebih Dahulu --</option>

                            @foreach ($kitchens as $kitchen)
                                {{-- PENTING: data-region-id harus sesuai dengan ID region di loop atas --}}
                                {{-- Asumsi kolom foreign key di tabel kitchens adalah 'region_id' --}}
                                <option value="{{ $kitchen->kode }}" 
                                        data-region-id="{{ $kitchen->region_id }}"
                                        class="hidden">
                                    {{ $kitchen->nama }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('kitchens')" />
                        <x-input-error :messages="$errors->get('kitchens.0')" />
                    </div>

                    {{-- BUTTON --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="py-2 w-full bg-blue-500 font-normal text-base text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:bg-blue-600 active:bg-blue-700 transition duration-150 ease-in-out">
                            Register
                        </button>
                    </div>
                </form>

                <p class="font-normal text-sm text-center text-black">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-500 cursor-pointer hover:underline">Masuk disini</a>
                </p>
            </div>
        </div>
    </div>

    {{-- 3. JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const regionSelect = document.getElementById('region_filter');
            const kitchenSelect = document.getElementById('kitchens');
            
            // Simpan semua option dapur ke dalam array di memori agar mudah di-reset
            // Kita clone node-nya agar tidak hilang saat dimanipulasi
            const allKitchenOptions = Array.from(kitchenSelect.querySelectorAll('option')).slice(1); // skip placeholder pertama
            
            // Placeholder asli dapur
            const defaultPlaceholder = kitchenSelect.querySelector('option[disabled]');

            regionSelect.addEventListener('change', function() {
                const selectedRegionId = this.value;

                // Reset Kitchen Dropdown
                kitchenSelect.value = ""; 
                
                // Hapus semua option dapur yang ada sekarang (kecuali placeholder)
                kitchenSelect.innerHTML = "";
                kitchenSelect.appendChild(defaultPlaceholder);

                if (selectedRegionId) {
                    // Aktifkan dropdown dapur
                    kitchenSelect.disabled = false;
                    kitchenSelect.classList.remove('bg-gray-100');
                    kitchenSelect.classList.add('bg-white');
                    defaultPlaceholder.textContent = "-- Pilih Salah Satu Dapur --";

                    // Filter dan tambahkan kembali option yang sesuai region
                    allKitchenOptions.forEach(option => {
                        if (option.dataset.regionId == selectedRegionId) {
                            option.classList.remove('hidden'); // pastikan terlihat
                            kitchenSelect.appendChild(option);
                        }
                    });

                    // Cek jika tidak ada dapur di region tersebut
                    if (kitchenSelect.options.length === 1) {
                         const noDataOption = document.createElement('option');
                         noDataOption.text = "Tidak ada dapur di wilayah ini";
                         noDataOption.disabled = true;
                         kitchenSelect.appendChild(noDataOption);
                    }

                } else {
                    // Jika region di-reset ke "Pilih Wilayah Dahulu"
                    kitchenSelect.disabled = true;
                    kitchenSelect.classList.add('bg-gray-100');
                    defaultPlaceholder.textContent = "-- Pilih Wilayah Diatas Terlebih Dahulu --";
                }
            });
        });
    </script>
</x-guest-layout>