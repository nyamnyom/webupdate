@extends('layouts.admin_layout')

@section('title', 'Edit Barang Paket')

@section('content')
<h2>Edit Barang Paket</h2>

<form method="POST" action="{{ url('/admin/barang/paket/update/' . $barang->id) }}">
    @csrf

    <label>Nama Paket:</label><br>
    <input type="text" name="Nama_Barang" value="{{ $barang->nama }}" required><br><br>

    <label>Harga Paket:</label><br>
    <input type="number" name="harga" value="{{ $barang->harga }}" required><br><br>

    <h4>Komponen Barang:</h4>
    <table border="1" cellpadding="8" cellspacing="0" id="komponen-table">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>ID</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komponen as $i => $k)
                <tr>
                    <td><input type="text" class="barang-nama" value="{{ $k->nama }}" readonly></td>
                    <td><input type="text" name="komponen[{{ $i }}][id]" value="{{ $k->id }}" readonly></td>
                    <td><input type="number" name="komponen[{{ $i }}][jumlah]" value="{{ $k->qty }}" min="0.01" step="0.01" required></td>
                    <td><button type="button" onclick="hapusBaris(this)">Hapus</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <label>Tambah Komponen Baru:</label><br>
    <input type="text" id="barang-input" placeholder="Cari barang..." autocomplete="off">
    <input type="hidden" id="barang-id">
    <input type="number" id="barang-jumlah" value="1" min="1">
    <button type="button" onclick="tambahKomponen()">Tambah</button>

    <br><br>
    <button type="submit">Simpan Perubahan</button>
</form>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">

<script>
    let komponenIndex = {{ count($komponen) }};

    $(function () {
        $("#barang-input").autocomplete({
    source: function (request, response) {
        $.getJSON("{{ url('/api/barang-search') }}", { term: request.term }, function (data) {
            // Ambil semua ID yang sudah ada di tabel
            const existingIds = [];
            $('#komponen-table tbody tr').each(function () {
                const id = $(this).find('input[name$="[id]"]').val();
                existingIds.push(id);
            });

            // Filter agar barang yang sudah ada tidak muncul lagi
            const filtered = data.filter(item => !existingIds.includes(item.id));

            response(filtered);
        });
    },
    minLength: 1,
    select: function (event, ui) {
        $('#barang-id').val(ui.item.id);
    }
});

    });

    function tambahKomponen() {
    const nama = $('#barang-input').val();
    const id = $('#barang-id').val();
    const jumlah = parseFloat($('#barang-jumlah').val());

    if (!nama || !id || id === "0" || isNaN(jumlah) || jumlah <= 0) {
        alert("Pilih barang dari hasil pencarian, dan isi jumlah > 0.");
        return;
    }

    // Cek duplikat
    const sudahAda = $('#komponen-table tbody input[name$="[id]"]').toArray().some(el => el.value === id);
    if (sudahAda) {
        alert("Barang sudah ada di daftar.");
        return;
    }

    const tbody = $('#komponen-table tbody');
    tbody.append(`
        <tr>
            <td><input type="text" class="barang-nama" value="${nama}" readonly></td>
            <td><input type="text" name="komponen[${komponenIndex}][id]" value="${id}" readonly></td>
            <td><input type="number" name="komponen[${komponenIndex}][jumlah]" value="${jumlah}" min="0.01" step="0.01"></td>
            <td><button type="button" onclick="hapusBaris(this)">Hapus</button></td>
        </tr>
    `);

    komponenIndex++;
    $('#barang-input').val('');
    $('#barang-id').val('');
    $('#barang-jumlah').val(1);
}


    function hapusBaris(button) {
        button.closest('tr').remove();
    }
</script>
@endsection
