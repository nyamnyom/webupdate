@extends('layouts.user_layout')

@section('title', 'Pelunasan Nota')

@section('content')
<h2>Pelunasan Nota</h2>

@if(session('success'))
    <div style="color: green;">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div style="color: red;">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ url('/user/pelunasan') }}">
    <label for="toko_nama">Pilih Nama Toko:</label>
    <select name="toko_nama" id="toko_nama" onchange="this.form.submit()">
        <option value="">-- Pilih Toko --</option>
        @foreach($tokoList as $toko)
            <option value="{{ $toko }}" {{ ($toko_nama == $toko) ? 'selected' : '' }}>{{ $toko }}</option>
        @endforeach
    </select>
</form>

@if($toko_nama)
    <div style="margin: 10px 0;">
        <label for="tanggal_global">Tanggal Pelunasan:</label>
        <input type="date" id="tanggal_global" value="{{ date('Y-m-d') }}">
    </div>
@endif

@if(count($notaList) > 0)
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID Nota</th>
                <th>No Kiriman</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Pelunasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notaList as $nota)
                <tr>
                    <td>{{ $nota->id }}</td>
                    <td>{{ $nota->nokiriman }}</td>
                    <td>{{ number_format($nota->total, 0) }}</td>
                    <td>{{ $nota->status }}</td>
                    <td>{{ $nota->created_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('user.pelunasan.bayar') }}" class="form-pelunasan">
                            @csrf
                            <input type="hidden" name="nota_id" value="{{ $nota->id }}">
                            <input type="hidden" name="tanggal_bayar" class="input-tanggal">
                            <button type="submit">Tandai Lunas</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Tidak ada nota belum lunas untuk toko ini.</p>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const globalDateInput = document.getElementById('tanggal_global');
    const forms = document.querySelectorAll('.form-pelunasan');

    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const tanggalInput = form.querySelector('.input-tanggal');
            if(globalDateInput) {
                tanggalInput.value = globalDateInput.value;
            }
        });
    });
});
</script>
@endsection
