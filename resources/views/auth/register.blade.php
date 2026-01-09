<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        .register-container {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 50%, #FFFFFF 100%);
            overflow-y: auto;
            padding: 60px 20px;
        }

        /* Animated gradient overlay for fun effect */
        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(0, 201, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(30, 144, 255, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(100, 149, 237, 0.04) 0%, transparent 50%);
            animation: gradientShift 15s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.1) rotate(5deg);
            }
        }

        /* Animated Food Icons */
        .bg-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .food-icon {
            position: absolute;
            opacity: 0.15;
            animation: float 20s infinite ease-in-out;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.08));
        }

        .food-icon svg {
            width: 120px;
            height: 120px;
        }

        .food-1 {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .food-1 svg {
            width: 130px;
            height: 130px;
        }

        .food-2 {
            bottom: 15%;
            right: 8%;
            animation-delay: 3s;
        }

        .food-2 svg {
            width: 110px;
            height: 110px;
        }

        .food-3 {
            top: 60%;
            right: 10%;
            animation-delay: 6s;
        }

        .food-3 svg {
            width: 105px;
            height: 105px;
        }

        .food-4 {
            bottom: 40%;
            left: 8%;
            animation-delay: 9s;
        }

        .food-4 svg {
            width: 115px;
            height: 115px;
        }

        .food-5 {
            top: 30%;
            left: 15%;
            animation-delay: 12s;
        }

        .food-5 svg {
            width: 100px;
            height: 100px;
        }

        .food-6 {
            top: 70%;
            right: 20%;
            animation-delay: 15s;
        }

        .food-6 svg {
            width: 108px;
            height: 108px;
        }

        .food-7 {
            bottom: 10%;
            left: 30%;
            animation-delay: 18s;
        }

        .food-7 svg {
            width: 95px;
            height: 95px;
        }

        .food-8 {
            top: 15%;
            right: 25%;
            animation-delay: 4s;
        }

        .food-8 svg {
            width: 112px;
            height: 112px;
        }

        .food-9 {
            top: 45%;
            left: 25%;
            animation-delay: 7s;
        }

        .food-9 svg {
            width: 105px;
            height: 105px;
        }

        .food-10 {
            bottom: 35%;
            right: 15%;
            animation-delay: 11s;
        }

        .food-10 svg {
            width: 118px;
            height: 118px;
        }

        .food-11 {
            top: 25%;
            right: 5%;
            animation-delay: 14s;
        }

        .food-11 svg {
            width: 98px;
            height: 98px;
        }

        .food-12 {
            bottom: 20%;
            left: 18%;
            animation-delay: 16s;
        }

        .food-12 svg {
            width: 110px;
            height: 110px;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(30px, -30px) rotate(10deg) scale(1.1);
            }
            50% {
                transform: translate(-20px, 20px) rotate(-10deg) scale(0.95);
            }
            75% {
                transform: translate(40px, 10px) rotate(5deg) scale(1.05);
            }
        }

        /* Glass Morphism Card */
        .register-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
            padding: 40px 50px;
            margin: auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 
                0 20px 60px rgba(0, 100, 200, 0.12),
                0 8px 30px rgba(30, 144, 255, 0.08),
                0 0 0 1px rgba(30, 144, 255, 0.1) inset;
            animation: slideUp 0.8s ease-out;
            border: 1px solid rgba(30, 144, 255, 0.1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Decorative Element */
        .card-decoration {
            position: absolute;
            top: -3px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 6px;
            background: linear-gradient(90deg, #00C9FF, #1E90FF);
            border-radius: 0 0 10px 10px;
            box-shadow: 0 3px 10px rgba(0, 201, 255, 0.3);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #00C9FF 0%, #1E90FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            animation: fadeIn 1s ease-out 0.3s both;
            letter-spacing: -0.5px;
        }

        .register-subtitle {
            font-size: 14px;
            color: #64748b;
            font-weight: 400;
            line-height: 1.6;
            animation: fadeIn 1s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 16px;
            animation: fadeIn 1s ease-out 0.7s both;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            color: #1e293b;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #1E90FF;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(30, 144, 255, 0.12);
        }

        .form-input:hover,
        .form-select:hover {
            border-color: #cbd5e1;
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='%23334155' d='M4.427 6.427l3.396 3.396a.25.25 0 00.354 0l3.396-3.396A.25.25 0 0011.396 6H4.604a.25.25 0 00-.177.427z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            padding-right: 45px;
        }

        .form-select:disabled {
            background-color: #f1f5f9;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .error-message {
            font-size: 12px;
            color: #ef4444;
            margin-top: 6px;
            display: block;
        }

        /* Submit Button */
        .submit-button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #00C9FF 0%, #1E90FF 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 201, 255, 0.35);
            font-family: 'Poppins', sans-serif;
            margin-top: 8px;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 201, 255, 0.45);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        /* Login Link */
        .login-text {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-top: 20px;
        }

        .login-link {
            color: #1E90FF;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: #00C9FF;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .register-container {
                padding: 30px 15px;
            }

            .register-card {
                width: 95%;
                padding: 30px 25px;
            }

            .register-title {
                font-size: 28px;
            }

            .form-group {
                margin-bottom: 14px;
            }

            .form-input,
            .form-select {
                padding: 11px 14px;
            }
        }
    </style>

    <div class="register-container">
        <!-- Animated Food Icons -->
        <div class="bg-shapes">
            <!-- Salad Bowl -->
            <div class="food-icon food-1">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="36" r="24" fill="#90EE90"/>
                    <path d="M16 36C16 36 20 28 32 28C44 28 48 36 48 36" stroke="#228B22" stroke-width="2"/>
                    <ellipse cx="26" cy="32" rx="4" ry="3" fill="#FF6347"/>
                    <ellipse cx="38" cy="34" rx="3" ry="2.5" fill="#FFD700"/>
                    <path d="M12 36H52C52 45 43 52 32 52C21 52 12 45 12 36Z" fill="#98FB98"/>
                </svg>
            </div>
            
            <!-- Carrot -->
            <div class="food-icon food-2">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32 12L28 20C26 28 24 36 24 44C24 52 28 56 32 56C36 56 40 52 40 44C40 36 38 28 36 20L32 12Z" fill="#FF8C00"/>
                    <path d="M30 10C30 10 26 6 24 8C22 10 26 14 26 14" stroke="#228B22" stroke-width="2" fill="#32CD32"/>
                    <path d="M34 10C34 10 38 6 40 8C42 10 38 14 38 14" stroke="#228B22" stroke-width="2" fill="#32CD32"/>
                    <line x1="28" y1="24" x2="30" y2="26" stroke="#FFA500" stroke-width="1.5"/>
                    <line x1="34" y1="30" x2="36" y2="32" stroke="#FFA500" stroke-width="1.5"/>
                </svg>
            </div>
            
            <!-- Tomato -->
            <div class="food-icon food-3">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="36" r="20" fill="#FF6347"/>
                    <path d="M28 16C28 16 30 12 32 12C34 12 36 16 36 16" stroke="#228B22" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M26 16L32 20L38 16" fill="#32CD32"/>
                    <ellipse cx="28" cy="32" rx="3" ry="4" fill="#FF4500" opacity="0.3"/>
                </svg>
            </div>
            
            <!-- Broccoli -->
            <div class="food-icon food-4">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="26" cy="24" r="8" fill="#228B22"/>
                    <circle cx="38" cy="24" r="8" fill="#228B22"/>
                    <circle cx="32" cy="18" r="7" fill="#32CD32"/>
                    <circle cx="32" cy="30" r="8" fill="#228B22"/>
                    <path d="M28 36L30 52C30 52 32 54 34 52L36 36" fill="#90EE90" stroke="#228B22" stroke-width="1.5"/>
                </svg>
            </div>
            
            <!-- Avocado -->
            <div class="food-icon food-5">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32 12C24 12 16 20 16 32C16 44 24 56 32 56C40 56 48 44 48 32C48 20 40 12 32 12Z" fill="#6B8E23"/>
                    <ellipse cx="32" cy="34" rx="10" ry="12" fill="#9ACD32"/>
                    <circle cx="32" cy="34" r="5" fill="#8B4513"/>
                </svg>
            </div>
            
            <!-- Cooking Pot -->
            <div class="food-icon food-6">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="16" y="28" width="32" height="24" rx="4" fill="#C0C0C0"/>
                    <rect x="14" y="26" width="36" height="4" fill="#A9A9A9"/>
                    <circle cx="20" cy="24" r="2" fill="#696969"/>
                    <circle cx="44" cy="24" r="2" fill="#696969"/>
                    <path d="M24 32C24 32 28 36 32 36C36 36 40 32 40 32" stroke="#FFD700" stroke-width="2"/>
                </svg>
            </div>
            
            <!-- Apple -->
            <div class="food-icon food-7">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="36" r="18" fill="#DC143C"/>
                    <path d="M32 18C32 18 30 14 32 12C34 10 36 14 36 18" stroke="#8B4513" stroke-width="2" stroke-linecap="round"/>
                    <path d="M34 16C34 16 38 14 40 16C40 16 38 18 36 18" fill="#228B22"/>
                    <ellipse cx="26" cy="32" rx="4" ry="5" fill="#FF6B6B" opacity="0.4"/>
                </svg>
            </div>
            
            <!-- Egg -->
            <div class="food-icon food-8">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="32" cy="36" rx="16" ry="22" fill="#FFFACD"/>
                    <ellipse cx="32" cy="36" rx="14" ry="20" fill="#FFFFE0"/>
                    <ellipse cx="28" cy="32" rx="3" ry="4" fill="#FFF" opacity="0.6"/>
                </svg>
            </div>

            <!-- Strawberry -->
            <div class="food-icon food-9">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32 16C28 16 20 20 18 28C16 36 20 52 32 56C44 52 48 36 46 28C44 20 36 16 32 16Z" fill="#DC143C"/>
                    <path d="M26 14L32 18L38 14C38 14 36 10 32 10C28 10 26 14 26 14Z" fill="#32CD32"/>
                    <circle cx="26" cy="28" r="1.5" fill="#FFD700"/>
                    <circle cx="32" cy="32" r="1.5" fill="#FFD700"/>
                    <circle cx="38" cy="28" r="1.5" fill="#FFD700"/>
                    <circle cx="28" cy="38" r="1.5" fill="#FFD700"/>
                    <circle cx="36" cy="38" r="1.5" fill="#FFD700"/>
                    <circle cx="32" cy="44" r="1.5" fill="#FFD700"/>
                </svg>
            </div>

            <!-- Fish -->
            <div class="food-icon food-10">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="36" cy="32" rx="20" ry="12" fill="#4682B4"/>
                    <path d="M16 32L26 28L26 36L16 32Z" fill="#4682B4"/>
                    <circle cx="44" cy="30" r="2" fill="#000"/>
                    <path d="M52 28L58 32L52 36L52 28Z" fill="#5F9EA0"/>
                    <path d="M30 24C30 24 34 28 36 28" stroke="#87CEEB" stroke-width="1.5"/>
                    <path d="M30 32C30 32 34 32 38 32" stroke="#87CEEB" stroke-width="1.5"/>
                    <path d="M30 40C30 40 34 36 36 36" stroke="#87CEEB" stroke-width="1.5"/>
                </svg>
            </div>

            <!-- Orange -->
            <div class="food-icon food-11">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="36" r="20" fill="#FF8C00"/>
                    <path d="M32 16C32 16 30 12 32 10C34 8 36 12 36 16" stroke="#228B22" stroke-width="2" stroke-linecap="round"/>
                    <path d="M30 16C30 16 34 14 36 16" fill="#32CD32"/>
                    <circle cx="32" cy="36" r="15" fill="#FFA500" opacity="0.5"/>
                    <path d="M32 21L32 51M17 36L47 36" stroke="#FF8C00" stroke-width="1" opacity="0.3"/>
                </svg>
            </div>

            <!-- Mushroom -->
            <div class="food-icon food-12">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="32" cy="28" rx="20" ry="14" fill="#DC143C"/>
                    <circle cx="24" cy="24" r="3" fill="#FFF" opacity="0.8"/>
                    <circle cx="36" cy="22" r="2.5" fill="#FFF" opacity="0.8"/>
                    <circle cx="40" cy="28" r="2" fill="#FFF" opacity="0.8"/>
                    <rect x="26" y="28" width="12" height="24" rx="6" fill="#F5DEB3"/>
                    <ellipse cx="32" cy="52" rx="8" ry="3" fill="#DEB887"/>
                </svg>
            </div>
        </div>

        <!-- Register Card -->
        <div class="register-card">
            <div class="card-decoration"></div>
            
            <div class="register-header">
                <h1 class="register-title">Daftar Akun</h1>
                <p class="register-subtitle">
                    Masukkan data-data yang diperlukan
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                
                <!-- Nama Field -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}" 
                            class="form-input"
                            placeholder="Masukkan nama lengkap"
                            autofocus 
                        />
                    </div>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        Email <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            class="form-input"
                            placeholder="nama@example.com"
                        />
                    </div>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        Password <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            class="form-input"
                            placeholder="••••••••"
                        />
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Confirmation Field -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        Konfirmasi Password <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            class="form-input"
                            placeholder="••••••••"
                        />
                    </div>
                    @error('password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Region Filter -->
                <div class="form-group">
                    <label for="region_filter" class="form-label">
                        Pilih Wilayah (Region)
                    </label>
                    <select id="region_filter" class="form-select">
                        <option value="" selected>-- Pilih Wilayah Dahulu --</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}">
                                {{ $region->nama_region }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kitchen Select -->
                <div class="form-group">
                    <label for="kitchens" class="form-label">
                        Pilih Dapur <span class="required">*</span>
                    </label>
                    <select name="kitchens[]" id="kitchens" required disabled class="form-select">
                        <option value="" disabled selected>-- Pilih Wilayah Diatas Terlebih Dahulu --</option>
                        @foreach ($kitchens as $kitchen)
                            <option value="{{ $kitchen->kode }}" 
                                    data-region-id="{{ $kitchen->region_id }}"
                                    class="hidden">
                                {{ $kitchen->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kitchens')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    @error('kitchens.0')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-button">
                    Daftar Sekarang
                </button>
            </form>

            <!-- Login Link -->
            <p class="login-text">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="login-link">Masuk disini</a>
            </p>
        </div>
    </div>

    {{-- JavaScript Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const regionSelect = document.getElementById('region_filter');
            const kitchenSelect = document.getElementById('kitchens');
            
            // Simpan semua option dapur ke dalam array di memori agar mudah di-reset
            const allKitchenOptions = Array.from(kitchenSelect.querySelectorAll('option')).slice(1);
            
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
                    defaultPlaceholder.textContent = "-- Pilih Salah Satu Dapur --";

                    // Filter dan tambahkan kembali option yang sesuai region
                    allKitchenOptions.forEach(option => {
                        if (option.dataset.regionId == selectedRegionId) {
                            option.classList.remove('hidden');
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
                    defaultPlaceholder.textContent = "-- Pilih Wilayah Diatas Terlebih Dahulu --";
                }
            });
        });
    </script>
</x-guest-layout>