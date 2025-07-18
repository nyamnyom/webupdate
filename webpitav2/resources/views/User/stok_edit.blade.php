@extends('layouts.user_layout')

@section('title', 'Edit Barang')

@section('content')
<h2>Edit Barang</h2>

<form method="POST" action="{{ url('/user/stok/'.$barang->id.'/update') }}">
    @csrf
    <label>Nama:</label><br>
    <input type="text" name="nama" value="{{ $barang->nama }}" required><br><br>

    <label>Stok:</label><br>
    <input type="number" name="stok" value="{{ $barang->stok }}" min="0" required><br><br>

    <label>Harga:</label><br>
    <input type="number" name="harga" value="{{ $barang->harga }}" required><br><br>

    <button type="submit">Update Barang</button>
</form>

<a href="{{ url('/user/stok') }}">Kembali ke Stok</a>
@endsection
