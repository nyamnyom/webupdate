@extends('layouts.user_layout')
@section('title', 'Retur Barang')

@section('content')
<div class="container">
    <h2>Form Retur Barang</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('user.retur') }}">
        <label for="toko_nama">Pilih Toko:</label>
        <select name="toko_nama" onchange="this.form.submit()">
            <option value="">--Pilih--</option>
            @foreach($tokoList as $toko)
                <option value="{{ $toko->nama_toko }}" {{ $selectedToko == $toko->nama_toko ? 'selected' : '' }}>
                    {{ $toko->nama_toko }}
                </option>
            @endforeach
        </select>

        @if($notaList)
            <label for="nokiriman">No Kiriman:</label>
            <select name="nokiriman" onchange="this.form.submit()">
                <option value="">--Pilih--</option>
                @foreach($notaList as $nota)
                    <option value="{{ $nota->nokiriman }}" {{ $selectedNota == $nota->nokiriman ? 'selected' : '' }}>
                        {{ $nota->nokiriman }}
                    </option>
                @endforeach
            </select>
        @endif
    </form>

    @if($notaBarang)
    <form method="POST" action="{{ route('user.retur.submit') }}">
        @csrf
        <input type="hidden" name="toko_nama" value="{{ $selectedToko }}">
        <input type="hidden" name="nokiriman" value="{{ $selectedNota }}">

        <h4>Barang dari Nota</h4>
        @foreach($notaBarang as $i => $barang)
        <div style="margin-bottom:10px;">
            @php
                $barangId = DB::table('barang')->where('nama', $barang->barang)->value('id');
            @endphp
            <input type="hidden" name="barang_id[]" value="{{ $barangId }}">
            <input type="hidden" name="is_tambahan[]" value="0">
            {{ $barang->barang }} - Qty Beli: {{ $barang->qty }}
            <input type="number" step="0.01" name="qty[]" placeholder="Qty retur">
        </div>
        @endforeach

        <hr>
        <h4>Tambah Barang Lain</h4>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
            <div style="flex: 2; min-width: 200px;">
                <label>Nama Barang:</label><br>
                <select id="barang-select" class="form-control" style="width: 100%;">
                    <option value="">-- Pilih Barang --</option>
                    @foreach($semuaBarang as $barang)
                        @php
                            $sudahAda = collect($notaBarang)->pluck('barang')->contains($barang->nama);
                        @endphp
                        @if(!$sudahAda)
                            <option value="{{ $barang->id }}">{{ $barang->nama }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div style="flex: 1;">
                <label>Qty:</label><br>
                <input type="number" step="0.01" id="barang-qty" class="form-control">
            </div>
            <div style="flex: 0.5;">
                <br>
                <button type="button" onclick="addBarangTambahan()">Tambah</button>
            </div>
        </div>

        <div id="barang-tambahan-container"></div>

        <br>
        <button type="submit">Simpan Retur</button>
    </form>
    @endif
</div>

{{-- jQuery + Select2 --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#barang-select').select2({
        placeholder: '-- Pilih Barang --',
        width: 'resolve'
    });
});

function addBarangTambahan() {
    const select = document.getElementById('barang-select');
    const qtyInput = document.getElementById('barang-qty');
    const container = document.getElementById('barang-tambahan-container');

    const barangId = select.value;
    const barangNama = select.options[select.selectedIndex].text;
    const qty = qtyInput.value;

    if (!barangId || !qty || parseFloat(qty) <= 0) {
        alert("Pilih barang dan masukkan qty yang valid.");
        return;
    }

    const div = document.createElement('div');
    div.innerHTML = `
        <div style="margin-bottom:10px;">
            ${barangNama} - Qty: ${qty}
            <input type="hidden" name="barang_id[]" value="${barangId}">
            <input type="hidden" name="qty[]" value="${qty}">
            <input type="hidden" name="is_tambahan[]" value="1">
        </div>
    `;
    container.appendChild(div);

    // Reset input
    select.selectedIndex = 0;
    $('#barang-select').val(null).trigger('change');
    qtyInput.value = "";
}
</script>
@endsection
