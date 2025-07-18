@extends('layouts.admin_layout')

@section('title', 'Tambah User')

@section('content')
<h2>Tambah User</h2>

<form method="POST" action="{{ url('/admin/user/store') }}">
    @csrf

    <label>Nama:</label><br>
    <input type="text" name="nama" required><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Akses:</label><br>
    <label><input type="checkbox" name="nota" value="1"> Nota</label><br>
    <label><input type="checkbox" name="retur" value="1"> Retur</label><br>
    <label><input type="checkbox" name="stok" value="1"> Stok</label><br>
    <label><input type="checkbox" name="pelunasan" value="1"> Pelunasan</label><br><br>

    <button type="submit">Simpan</button>
</form>

<a href="{{ url('/admin/user') }}">Kembali</a>
@endsection
