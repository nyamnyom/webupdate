@extends('layouts.admin_layout')

@section('title', 'Daftar Sales')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Sales</h2>
        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">Tambah Sales</a>
    </div>

    <table class="table table-bordered table-striped" border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->username }}</td>
                    <td>{{ $s->created_at }}</td>
                    <td>
                        <form action="{{ route('admin.sales.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus sales ini?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada sales</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
