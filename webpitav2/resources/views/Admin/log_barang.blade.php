@extends('layouts.admin_layout')

@section('title', 'Log Barang')

@section('content')
<h2>Log Barang</h2>

<form method="GET" action="{{ url('/admin/log-barang') }}" style="margin-bottom: 20px;">

    <label>Nama Barang:</label>
    <input type="text" name="nama_barang" value="{{ request('nama_barang') }}">
    <br><br>

    <label>Dari Tanggal:</label>
    <input type="date" name="start_date" value="{{ request('start_date') }}">

    <label>Sampai Tanggal:</label>
    <input type="date" name="end_date" value="{{ request('end_date') }}">

    

    <button type="submit">Filter</button>
    <a href="{{ url('/admin/log-barang') }}">Reset</a>
</form>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Barang ID</th>
            <th>Nama Barang</th>
            <th>Keterangan</th>
            <th>Stok Masuk</th>
            <th>Stok Keluar</th>
            <th>Stok Setelahnya</th>
            <th>Oleh</th>
        </tr>
    </thead>
    <tbody>

        {{-- Tampilkan stok sebelumnya --}}
        @foreach($stokSebelumnya as $stok)
        <tr style="background-color: #f0f0f0;">
            <td>-</td>
            <td>{{ $stok['barang_id'] }}</td>
            <td>{{ $stok['nama_barang'] }}</td>
            <td>Stok Sebelumnya</td>
            <td>0</td>
            <td>0</td>
            <td>{{ $stok['stok_after'] }}</td>
            <td>-</td>
        </tr>
        @endforeach

        {{-- Tampilkan log setelah filter --}}
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->created_at }}</td>
            <td>{{ $log->barang_id }}</td>
            <td>{{ $log->nama_barang }}</td>
            <td>{{ $log->keterangan }}</td>
            <td>{{ $log->stok_in }}</td>
            <td>{{ $log->stok_out }}</td>
            <td>{{ $log->stok_after }}</td>
            <td>{{ $log->by_who }}</td>
        </tr>
        @endforeach

    </tbody>
</table>
@endsection
