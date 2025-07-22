@extends('layouts.user_layout')

@section('title', 'Retur Barang')

@section('content')
<h2>Retur Barang</h2>

@if(session('success'))
    <div style="color: green;">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div style="color: red;">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ url('/user/retur') }}">
    <label for="toko_nama">Pilih Toko:</label>
    <select name="toko_nama" id="toko_nama" class="select2" onchange="this.form.submit()">
        <option value="">-- Cari Toko --</option>
        @foreach($tokoList as $toko)
            <option value="{{ $toko }}" {{ ($toko_nama == $toko) ? 'selected' : '' }}>{{ $toko }}</option>
        @endforeach
    </select>
</form>

@if($notaList)
    <form method="GET" action="{{ url('/user/retur') }}">
        <input type="hidden" name="toko_nama" value="{{ $toko_nama }}">
        <label for="nokiriman">Pilih No Kiriman:</label>
        <select name="nokiriman" id="nokiriman" class="select2" onchange="this.form.submit()">
            <option value="">-- Cari No Kiriman --</option>
            @foreach($notaList as $nota)
                <option value="{{ $nota->nokiriman }}" {{ ($nokiriman == $nota->nokiriman) ? 'selected' : '' }}>{{ $nota->nokiriman }}</option>
            @endforeach
        </select>
    </form>
@endif

@if($notaDetail)
    <h3>Detail Nota: No Kiriman {{ $notaDetail->nokiriman }}</h3>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Diskon</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barangList as $barang)
                <tr>
                    <td>{{ $barang->barang }}</td>
                    <td>{{ $barang->qty }}</td>
                    <td>Rp{{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td>{{ $barang->diskon}}%</td>
                    <td>Rp{{ number_format($barang->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <h3>Form Retur Barang</h3>
    <form method="POST" action="{{ route('user.retur.simpan') }}">
        @csrf
        <input type="hidden" name="nota_id" value="{{ $notaDetail->id }}">

        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Qty Dibeli</th>
                    <th>Qty Retur</th>
                </tr>
            </thead>
            <tbody>
                @foreach($barangList as $barang)
                    <tr>
                        <td>{{ $barang->barang }}</td>
                        <td>{{ $barang->qty }}</td>
                        <td>
                            <input type="number" name="qty_retur[{{ $barang->barang }}]" min="0" max="{{ $barang->qty }}" value="0">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <label for="keterangan">Keterangan:</label>
        <input type="text" name="keterangan" placeholder="Keterangan retur"><br><br>

        <button type="submit">Simpan Retur</button>
    </form>
@endif
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Cari...',
        allowClear: true,
        width: 'resolve'
    });
});
</script>
@endsection
