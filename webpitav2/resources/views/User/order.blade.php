@extends('layouts.user_layout')

@section('title', 'Buat Order')

@section('content')
@if(session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                timer: 2000,
                showConfirmButton: false
            });
        });
    </script>
@endif

@if(session('error'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session("error") }}',
            });
        });
    </script>
@endif

<h2>Buat Order Barang</h2>

<form method="POST" action="{{ url('/user/order') }}">
    @csrf

    {{-- Informasi Umum --}}
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 250px;">
            <label>Pilih Toko:</label><br>
            <select name="dari_toko" class="form-control" id="select-toko" required>
                <option value="">-- Pilih Toko --</option>
                @foreach($toko as $t)
                    <option value="{{ $t->nama_toko }}">{{ $t->nama_toko }} ({{ $t->kategori }})</option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label>No Kiriman:</label><br>
            <input type="text" name="nokiriman" required>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label>Pengerja:</label><br>
            <select name="pengerja[]" id="select-pengerja" class="form-control" multiple required>
                @foreach($sales as $s)
                    <option value="{{ $s->username }}">{{ $s->username }}</option>
                @endforeach
            </select>
            <small>Bisa pilih lebih dari satu</small>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label>Tanggal Order (opsional):</label><br>
            <input type="datetime-local" name="tanggal">
            <small>(Biarkan kosong untuk gunakan tanggal sekarang)</small>
        </div>
    </div>

    <hr><br>

    {{-- Input Barang --}}
    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
        <div style="flex: 2; min-width: 200px;">
            <label>Nama Barang:</label><br>
            <select id="barang-input" class="form-control" style="width: 100%;">
                <option value="">-- Pilih Barang --</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 120px;">
            <label>Jumlah:</label><br>
            <input type="number" id="barang-jumlah" min="0.1" step="0.1" value="1">
        </div>

        <div style="flex: 1; min-width: 120px;">
            <label>Diskon (%):</label><br>
            <input type="number" id="barang-diskon" min="0" max="100" step="1" value="0">
        </div>

        <div style="flex: 1; min-width: 120px;">
            <label>Harga Manual (opsional):</label><br>
            <input type="number" id="barang-harga" min="0" step="1">
        </div>

        <div style="flex: 1; min-width: 120px;">
            <button type="button" onclick="tambahBarang()">Tambah</button>
        </div>
    </div>

    <br><br>

    {{-- Tabel Daftar Barang --}}
    <table border="1" cellpadding="10" cellspacing="0" id="daftar-barang" class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>ID</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Diskon (%)</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <td colspan="5" align="right"><strong>Subtotal:</strong></td>
                <td colspan="2" id="subtotal-text">Rp 0</td>
            </tr>
        </tfoot>
    </table><br>

    <button type="submit">Kirim Order</button>
</form>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
let daftarBarang = [];

$(function () {
    // Select2 Toko
    $('#select-toko').select2({ width: '100%' });

    // Select2 Pengerja multiple
    $('#select-pengerja').select2({
        width: '100%',
        placeholder: "-- Pilih Pengerja --"
    });

    // Select2 Barang dengan AJAX (user API endpoint)
    $('#barang-input').select2({
        placeholder: "-- Pilih Barang --",
        allowClear: true,
        
        ajax: {
            url: "{{ url('api/order-search') }}", // sesuaikan endpoint user API
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.label
                    }))
                };
            },
            cache: true
        }
    });

    // Prevent Enter submit di form selain textarea
    $('form').on('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') {
            e.preventDefault();
        }
    });
});

function tambahBarang() {
    const id = $('#barang-input').val();
    const nama = $('#barang-input option:selected').text();
    const jumlah = parseFloat($('#barang-jumlah').val());
    const diskon = parseFloat($('#barang-diskon').val());
    const hargaManual = parseFloat($('#barang-harga').val());

    if (!id || !nama || isNaN(jumlah) || jumlah <= 0 || isNaN(diskon) || diskon < 0 || diskon > 100) {
        alert("Pastikan semua input valid.");
        return;
    }

    if (daftarBarang.some(b => b.id === id)) {
        alert("Barang sudah ada di daftar.");
        return;
    }

    if (!isNaN(hargaManual) && hargaManual > 0) {
        daftarBarang.push({ id, nama, jumlah, harga: hargaManual, diskon });
        renderDaftar();
        resetInput();
    } else {
        $.get(`/user/api/barang/${id}`, function (data) {
            const harga = parseFloat(data.harga);
            daftarBarang.push({ id, nama, jumlah, harga, diskon });
            renderDaftar();
            resetInput();
        }).fail(function () {
            alert("Gagal mengambil harga barang.");
        });
    }
}

function resetInput() {
    $('#barang-input').val(null).trigger('change');
    $('#barang-jumlah').val(1);
    $('#barang-diskon').val(0);
    $('#barang-harga').val('');
}

function renderDaftar() {
    const tbody = $('#daftar-barang tbody');
    tbody.empty();
    let subtotal = 0;

    daftarBarang.forEach((b, i) => {
        const hargaSetelahDiskon = b.harga * (1 - b.diskon / 100);
        const totalHarga = hargaSetelahDiskon * b.jumlah;
        subtotal += totalHarga;

        tbody.append(`
            <tr>
                <td>${b.nama}</td>
                <td>${b.id}</td>
                <td>${b.jumlah}</td>
                <td>${b.harga.toFixed(2)}</td>
                <td>${b.diskon}%</td>
                <td>${totalHarga.toFixed(2)}</td>
                <td><button type="button" onclick="hapus(${i})">Hapus</button></td>
            </tr>
            <input type="hidden" name="items[${i}][id]" value="${b.id}">
            <input type="hidden" name="items[${i}][jumlah]" value="${b.jumlah}">
            <input type="hidden" name="items[${i}][harga]" value="${b.harga}">
            <input type="hidden" name="items[${i}][diskon]" value="${b.diskon}">
        `);
    });

    $('#subtotal-text').text(`Rp ${subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}`);
}

function hapus(index) {
    daftarBarang.splice(index, 1);
    renderDaftar();
}
</script>
@endsection
