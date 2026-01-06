<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {

        $portals = [
            [
                'role' => 'Dashboard',
                'description' => 'Superadmin, Admin, dan Operator. Silakan masuk ke dashboard admin untuk mengelola data dan operasional sistem.',
                'icon' => 'fa-gauge-high',
                'color_theme' => 'blue',
                'url' => route('login'),
                'btn_text' => 'Masuk Dashboard'
            ],
            [
                'role' => 'Registrasi',
                'description' => 'Daftarkan akun anda disini, jangan lupa untuk meminta persetujuan admin terlebih dahulu.',
                'icon' => 'fa-solid fa-user-plus',
                'color_theme' => 'green',
                'url' => route('register'),
                'btn_text' => 'Registrasi Disini'
            ]
        ];
        return view('homepage.index', compact('portals'));
    }
}
