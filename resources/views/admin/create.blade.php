@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Tambah Admin Baru</h2>

    @if(session('success'))
        <div style="color: green; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.store') }}" method="POST">
        @csrf

        <label>Nama:</label>
        <input type="text" name="name" value="{{ old('name') }}">
        @error('name') <p style="color:red">{{ $message }}</p> @enderror
        <br>

        <label>Email:</label>
        <input type="email" name="email" value="{{ old('email') }}">
        @error('email') <p style="color:red">{{ $message }}</p> @enderror
        <br>

        <label>Password:</label>
        <input type="password" name="password">
        @error('password') <p style="color:red">{{ $message }}</p> @enderror
        <br><br>

        <button type="submit">Buat Admin</button>
    </form>

</div>
@endsection
