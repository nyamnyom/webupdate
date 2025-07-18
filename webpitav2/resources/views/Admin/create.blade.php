@extends('layouts.admin_layout')

@section('title', 'Tambah Barang')

@section('content')
<h2>Tambah Barang</h2>

<form method="POST" action="{{ url('/admin/barang') }}">
    @csrf
    <label>ID:</label><br>
    <input type="text" name="id" required><br>

    <label>Nama:</label><br>
    <input type="text" name="nama" required><br>

    <label>Stok:</label><br>
    <input type="number" name="stok" min="0.1" step="0.1" required><br>

    <label>Harga:</label><br>
    <input type="number" name="harga" required><br>

    <input type="hidden" name="tipe" value="barang">

    <button type="submit">Simpan</button>
</form>

<a href="{{ url('/admin/barang') }}">Kembali</a>
@endsection
