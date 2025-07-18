@extends('layouts.admin_layout')

@section('title', 'Manajemen Barang')

@section('content')
<h2>Daftar Barang</h2>

<a href="{{ url('/admin/barang/create') }}">+ Tambah Barang</a> | 
<a href="{{ url('/admin/barang/paket/create') }}">+ Tambah Paket</a>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Stok</th>
            <th>Harga</th>
            <th>Tipe</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($barang as $b)
        <tr>
            <td>{{ $b->id }}</td>
            <td>{{ $b->nama }}</td>
            <td>{{ $b->stok }}</td>
            <td>Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
            <td>{{ ucfirst($b->tipe) }}</td>
            <td>
                <a href="{{ url('/admin/barang/'.$b->id.'/edit') }}">Edit</a> |
                <form action="{{ url('/admin/barang/'.$b->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Yakin ingin hapus?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
