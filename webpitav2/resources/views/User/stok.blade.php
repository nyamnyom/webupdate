@extends('layouts.user_layout')

@section('title', 'Stok Barang')

@section('content')
<h2>Stok Barang</h2>

<a href="{{ route('user.barang.create') }}">+ Tambah Barang</a> |
<a href="{{ route('user.paket.create') }}">+ Tambah Paket</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Barang</th>
            <th>Stok</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($barang as $b)
        <tr>
            <td>{{ $b->id }}</td>
            <td>{{ $b->nama }}</td>
            <td>{{ $b->stok }}</td>
            <td>Rp{{ number_format($b->harga, 0, ',', '.') }}</td>
            <td>
                <a href="{{ url('/user/stok/'.$b->id.'/edit') }}">Edit</a> |
                <form action="{{ url('/user/stok/'.$b->id.'/delete') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Yakin ingin hapus?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
