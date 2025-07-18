@extends('layouts.user_layout')

@section('title', 'Tambah Barang')

@section('content')
<h2>Tambah Barang Baru</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ url('/user/barang/store') }}">
    @csrf

    <label>ID Barang:</label><br>
    <input type="text" name="id" required><br><br>

    <label>Nama Barang:</label><br>
    <input type="text" name="nama" required><br><br>

    <label>Stok:</label><br>
    <input type="number" name="stok" min="0.1" step="0.1" required><br><br>

    <label>Harga:</label><br>
    <input type="number" name="harga" min="0" required><br><br>

    <button type="submit">Simpan</button>
</form>

<a href="{{ url('/user/stok') }}">Kembali ke Stok</a>
@endsection
