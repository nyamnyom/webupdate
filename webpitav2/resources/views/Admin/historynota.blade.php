@extends('layouts.admin_layout')

@section('title', 'Riwayat Nota')

@section('content')
<div class="container">
    <h2>Riwayat Nota</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered" border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>No Kiriman</th>
                <th>Toko</th>
                <th>Total</th>
                <th>Status</th>
                <th>Pengerja</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notas as $i => $nota)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $nota->nokiriman }}</td>
                <td>{{ $nota->toko_id ?? '-' }}</td>
                <td>Rp {{ number_format($nota->total, 0, ',', '.') }}</td>
                <td>{{$nota->STATUS}}</td>
                
                <td>{{ $nota->pengerja }}</td>
                <td>{{ date('d-m-Y', strtotime($nota->created_at)) }}</td>
                <td>
                    @if($nota->STATUS !== 'cancel')
                    <form action="{{ route('admin.historynota.cancel', $nota->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin batalkan nota ini?')">Cancel</button>
                    </form>
                    @endif
                    <a href="{{ route('admin.historynota.edit', $nota->id) }}" class="btn btn-warning btn-sm">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
