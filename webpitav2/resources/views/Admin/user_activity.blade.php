@extends('layouts.admin_layout')

@section('title', 'User Activity Log')

@section('content')
<h2>Log Aktivitas User</h2>

<form method="GET" action="{{ url('/admin/user-activity') }}" style="margin-bottom: 20px;">
    <label>Username:</label>
    <input type="text" name="username" value="{{ request('username') }}">

    <label>Dari Tanggal:</label>
    <input type="date" name="start_date" value="{{ request('start_date') }}">

    <label>Sampai Tanggal:</label>
    <input type="date" name="end_date" value="{{ request('end_date') }}">

    <button type="submit">Filter</button>
    <a href="{{ url('/admin/user-activity') }}">Reset</a>
</form>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Username</th>
            <th>Nama User</th>
            <th>Aktivitas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->username }}</td>
                <td>{{ $log->nama }}</td>
                <td>{{ $log->aktivitas }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Tidak ada aktivitas ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
