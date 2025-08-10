@extends('layouts.user_layout')

@section('title', 'Daftar Sales')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Sales</h2>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">Tambah Sales</a>
    </div>

    <table class="table table-bordered table-striped" border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->username }}</td>
                    <td>{{ $s->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Belum ada sales</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
