@extends('layouts.admin_layout')

@section('title', 'Manajemen Barang')

@section('content')
<h2>Daftar Barang</h2>

<a href="{{ url('/admin/barang/create') }}">+ Tambah Barang</a> | 
<a href="{{ url('/admin/barang/paket/create') }}">+ Tambah Paket</a> |
<a href="{{ route('barang.export') }}">Export Excel</a>

{{-- Form Import Excel --}}
<form action="{{ route('barang.import') }}" method="POST" enctype="multipart/form-data" style="margin-top:10px; margin-bottom:10px;">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Import Excel</button>
    <br>
    <small>Format file: <b>id | nama_barang | stok | harga</b></small>
</form>

{{-- Search bar --}}
<div style="margin-bottom: 20px;">
    <label for="search-barang">Cari Barang (realtime):</label><br>
    <input type="text" id="search-barang" placeholder="Masukkan kata kunci (pisah dengan spasi)" style="padding:5px; width: 300px;">
    <button id="btn-reset" style="padding:5px 10px;">Reset</button>
</div>

<table border="1" cellpadding="5" cellspacing="0" id="barang-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Stok</th>
            <th>Harga</th>
            <th>Tipe</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($barang as $b)
        <tr>
            <td>{{ $b->id }}</td>
            <td>{{ $b->nama }}</td>
            <td>{{ $b->stok }}</td>
            <td>Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
            <td>{{ ucfirst($b->tipe) }}</td>
            <td>
                <a href="{{ url('/admin/barang/'.$b->id.'/edit') }}">Edit</a> |
                <form action="{{ url('/admin/barang/'.$b->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Yakin ingin hapus?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
const input = document.getElementById('search-barang');
const resetBtn = document.getElementById('btn-reset');
const rows = document.querySelectorAll('#barang-table tbody tr');

let debounceTimer;

input.addEventListener('input', () => {
    clearTimeout(debounceTimer);

    // Jangan langsung search kalau cuma spasi tunggal
    if(input.value === ' ') {
        return; // abaikan
    }

    debounceTimer = setTimeout(() => {
        const inputVal = input.value.trim().toLowerCase();

        if (inputVal === '') {
            showAllRows();
            return;
        }

        const keywords = inputVal.split(/\s+/).filter(k => k !== '');

        rows.forEach(row => {
            const nama = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const cocok = keywords.every(k => nama.includes(k));
            row.style.display = cocok ? '' : 'none';
        });
    }, 100); // delay 400ms setelah berhenti ketik
});

resetBtn.addEventListener('click', () => {
    input.value = '';
    showAllRows();
});

function showAllRows() {
    rows.forEach(row => {
        row.style.display = '';
    });
}
</script>

@endsection
