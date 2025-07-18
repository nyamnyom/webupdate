@extends('layouts.admin_layout')

@section('title', 'Manajemen User')

@section('content')
<h2>Manajemen User</h2>

<a href="{{ route('admin.user.create') }}">+ Tambah User</a>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Akses</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->nama }}</td>
            <td>{{ $u->username }}</td>
            <td>
                @php
                    $akses = [];
                    if ($u->nota) $akses[] = 'Nota';
                    if ($u->retur) $akses[] = 'Retur';
                    if ($u->stok) $akses[] = 'Stok';
                    if ($u->pelunasan) $akses[] = 'Pelunasan';
                @endphp
                {{ implode(', ', $akses) }}
            </td>
            <td>
                <a href="{{ route('admin.user.edit', $u->id) }}">Edit</a> |
                <form action="{{ route('admin.user.destroy', $u->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Yakin hapus user ini?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
