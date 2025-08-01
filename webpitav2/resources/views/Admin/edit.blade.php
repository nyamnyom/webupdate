@extends('layouts.admin_layout')

@section('title', 'Edit Barang')

@section('content')
<h2>Edit Barang</h2>

<form method="POST" action="{{ route('barang.update', $barang->id) }}">
    @csrf
    @method('PUT')
    <label>Nama Barang:</label><br>
    <input type="text" name="nama" value="{{ $barang->nama }}" required><br><br> 
    <label>Stok:</label><br>
    <input type="number" name="stok" value="{{ $barang->stok }}" required><br><br>
    <label>Harga:</label><br>
    <input type="number" name="harga" value="{{ $barang->harga }}" required><br>
    <input type="hidden" name="tipe" value="barang"><br><br>

    <button type="submit">Simpan</button>
</form>

<a href="{{ url('/admin/barang') }}">Kembali</a>
@endsection
