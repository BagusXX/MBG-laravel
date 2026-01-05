@extends('layouts.homepage')

@section('title', 'Portal Masuk - MBG Nasional')

@section('content')
    <div class="text-center mb-10 transform transition-all hover:scale-105 duration-500">
        <div class="w-24 h-24 mx-auto mb-4">
            <img src="{{ asset('vendor/adminlte/dist/img/Logo_mbg.png') }}" alt="">
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight mb-2">
            DapoerMBG
        </h1>
        <p class="text-slate-500 text-base md:text-lg max-w-lg mx-auto">
            Portal satu pintu untuk monitoring, distribusi, dan manajemen program Makan Bergizi Gratis.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-5xl">

        @foreach ($portals as $portal)
            @php
                $color = $portal['color_theme']; // blue, green, orange
                // Mapping class tailwind untuk hover dan text agar dinamis
                $hoverShadow = "hover:shadow-{$color}-200/50";
                $bgIcon = "bg-{$color}-50";
                $textIcon = "text-{$color}-600";
                $hoverBgIcon = "group-hover:bg-{$color}-600";
                $textBtn = "text-{$color}-600";
                $borderBtn = "border-{$color}-100";
                $hoverBgBtn = "group-hover:bg-{$color}-50";
                $textArrow = "text-{$color}-500";
            @endphp

            <a href="{{ $portal['url'] }}"
                class="group relative bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 hover:shadow-2xl {{ $hoverShadow }} hover:-translate-y-2 transition-all duration-300 flex flex-col items-center text-center">

                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity {{ $textArrow }}">
                    <i class="fas fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-300"></i>
                </div>

                <div
                    class="w-16 h-16 {{ $bgIcon }} {{ $textIcon }} rounded-2xl flex items-center justify-center text-2xl mb-4 {{ $hoverBgIcon }} group-hover:text-white transition-colors duration-300">
                    <i class="fas {{ $portal['icon'] }}"></i>
                </div>

                <h3 class="text-lg font-bold text-slate-800 mb-2">{{ $portal['role'] }}</h3>

                <p class="text-sm text-slate-500 leading-relaxed min-h-[40px]">
                    {{ $portal['description'] }}
                </p>

                <span
                    class="mt-6 text-xs font-bold uppercase tracking-wider {{ $textBtn }} border {{ $borderBtn }} px-3 py-1 rounded-full {{ $hoverBgBtn }} transition">
                    {{ $portal['btn_text'] }}
                </span>
            </a>
        @endforeach

    </div>


    <p class="mt-8 text-xs text-slate-400">© {{ date('Y') }} DapoerMBG. V.1.0.2</p>
    </div>
@endsection
