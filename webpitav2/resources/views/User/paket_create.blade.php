@extends('layouts.user_layout')

@section('title', 'Tambah Barang Paket')

@section('content')
<h2>Tambah Barang Paket</h2>

@if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

<form method="POST" action="{{ url('/user/paket/store') }}">
    @csrf

    <label>Nama Paket:</label><br>
    <input type="text" name="Nama_Barang" required><br><br>

    <label>Harga Paket:</label><br>
    <input type="number" name="harga" required><br><br>

    <div>
        <label>Tambah Komponen Barang:</label><br>
        <input type="text" id="barang-input" placeholder="Ketik nama barang..." autocomplete="off">
        <input type="hidden" id="barang-id">
        <label>Jumlah:</label><br>
        <input type="number" id="barang-jumlah" value="1" min="0.01" step="0.01">
        <button type="button" onclick="tambahKomponen()">Tambah</button>
    </div>

    <br>
    <table border="1" cellpadding="10" cellspacing="0" id="daftar-barang">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>ID</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <br><button type="submit">Simpan Paket</button>
</form>

<a href="{{ url('/user/stok') }}">Kembali ke Stok</a>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">

<script>
    let daftarKomponen = [];

    $(function() {
        $("#barang-input").autocomplete({
            source: "{{ url('/api/barang-search') }}",
            minLength: 1,
            select: function(event, ui) {
                $('#barang-id').val(ui.item.id);
            }
        });
    });

    function tambahKomponen() {
        const nama = $('#barang-input').val().trim();
        const id = $('#barang-id').val();
        const jumlah = parseFloat($('#barang-jumlah').val());

        if (!nama || !id || isNaN(jumlah) || jumlah <= 0) {
            alert("Isi nama barang yang valid dan jumlah > 0");
            return;
        }

        if (daftarKomponen.some(b => b.id === id)) {
            alert("Barang sudah ada dalam paket");
            return;
        }

        daftarKomponen.push({ id, nama, jumlah });
        renderDaftar();

        $('#barang-input').val('');
        $('#barang-id').val('');
        $('#barang-jumlah').val(1);
    }

    function renderDaftar() {
        const tbody = $('#daftar-barang tbody');
        tbody.empty();

        daftarKomponen.forEach((b, i) => {
            tbody.append(`
                <tr>
                    <td>${b.nama}</td>
                    <td>${b.id}</td>
                    <td>${b.jumlah}</td>
                    <td>
                        <button type="button" onclick="hapus(${i})">Hapus</button>
                        <input type="hidden" name="komponen[${i}][id]" value="${b.id}">
                        <input type="hidden" name="komponen[${i}][jumlah]" value="${b.jumlah}">
                    </td>
                </tr>
            `);
        });
    }

    function hapus(index) {
        daftarKomponen.splice(index, 1);
        renderDaftar();
    }
</script>
@endsection
