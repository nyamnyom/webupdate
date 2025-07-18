@extends('layouts.admin_layout')

@section('title', 'Edit Barang')

@section('content')
<h2>Edit Barang</h2>

<form method="POST" action="{{ url('/admin/barang/'.$barang->id) }}">
    @csrf
    @method('PUT')

    <label>Nama:</label><br>
    <input type="text" name="nama" value="{{ $barang->nama }}" required><br>

    <label>Stok:</label><br>
    <input type="number" name="stok" value="{{ $barang->stok }}" required><br>

    <label>Harga:</label><br>
    <input type="number" name="harga" value="{{ $barang->harga }}" required><br>

    

    <button type="submit">Update</button>
</form>

<a href="{{ url('/admin/barang') }}">Kembali</a>
@endsection
