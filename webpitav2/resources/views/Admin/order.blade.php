@extends('layouts.admin_layout')

@section('title', 'Create Order')

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

<form method="POST" action="{{ url('/admin/order') }}" id="form-order">
    @csrf

    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 250px;">
            <label for="select-toko">Pilih Toko:</label>
            <select name="dari_toko" class="form-control" id="select-toko" required>
                <option value="">-- Pilih Toko --</option>
                @foreach($toko as $t)
                    <option value="{{ $t->nama_toko }}">{{ $t->nama_toko }} ({{ $t->kategori }})</option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label for="nokiriman">No Kiriman:</label><br>
            <input type="text" name="nokiriman" id="nokiriman" class="form-control" required>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label for="pengerja">Pengerja:</label><br>
            <input type="text" name="pengerja" id="pengerja" class="form-control" required>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label for="tanggal">Tanggal Order (opsional):</label>
            <input type="datetime-local" name="tanggal" id="tanggal" class="form-control">
            <small>(Biarkan kosong untuk gunakan tanggal sekarang)</small>
        </div>
    </div>

    <hr><br>

    <h4>Tambah Barang</h4>
    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
        <div style="flex: 2; min-width: 220px;">
            <label for="barang-input">Nama Barang:</label>
            <select id="barang-input" class="form-control" style="width: 100%;" placeholder="Ketik nama barang...">
                <option value="">-- Pilih Barang --</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 60px;">
            <label for="barang-jumlah">Jumlah:</label>
            <input type="number" id="barang-jumlah" class="form-control" min="0.1" step="0.1" value="1">
        </div>
        
        <div style="flex: 1; min-width: 120px;">
            <label for="barang-diskon">Diskon (%):</label><br>
            <input type="number" id="barang-diskon" class="form-control" min="0" max="100" step="1" value="0">
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label for="barang-harga">Harga Manual (opsional):</label>
            <input type="number" id="barang-harga" class="form-control" min="0" step="1" placeholder="Harga satuan">
        </div>

        <div style="flex: 1; min-width: 120px;">
            <button type="button" class="btn btn-primary" onclick="tambahBarang()">Tambah</button>
        </div>
    </div>

    <br><br>

    <h4>Daftar Barang</h4>
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

    <button type="submit" class="btn btn-success">Kirim Order</button>
</form>
@endsection

@section('scripts')
<!-- JQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let daftarBarang = [];

$(document).ready(function () {
    // Select2 toko biasa
    $('#select-toko').select2({ width: '100%' });

    // Select2 barang dengan AJAX autocomplete
    $('#barang-input').select2({
        placeholder: "-- Pilih Barang --",
        allowClear: true,
        
        ajax: {
            url: "{{ url('/admin/api/order-search') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term // kata yang diketik
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

    // Cegah enter submit form saat pilih barang
    $('#barang-input').on('select2:select', function () {
        $('#barang-harga').focus();
    });

    // Cegah enter submit form secara tidak sengaja
    $('#form-order').on('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') {
            e.preventDefault();
            return false;
        }
    });
});

function tambahBarang() {
    const id = $('#barang-input').val();
    const nama = $('#barang-input option:selected').text();
    const jumlah = parseFloat($('#barang-jumlah').val());
    const diskon = parseFloat($('#barang-diskon').val());
    const hargaManual = parseFloat($('#barang-harga').val());

    if (!id || !nama) {
        alert("Pilih barang terlebih dahulu.");
        return;
    }
    if (isNaN(jumlah) || jumlah <= 0) {
        alert("Jumlah harus lebih dari 0.");
        return;
    }
    if (isNaN(diskon) || diskon < 0 || diskon > 100) {
        alert("Diskon harus antara 0 - 100.");
        return;
    }

    // Cek duplikat barang di daftar
    if (daftarBarang.some(b => b.id === id)) {
        alert("Barang sudah ada di daftar.");
        return;
    }

    if (!isNaN(hargaManual) && hargaManual > 0) {
        // Harga manual valid, langsung pakai
        daftarBarang.push({ id, nama, jumlah, harga: hargaManual, diskon });
        renderDaftar();
        resetInput();
    } else {
        // Ambil harga dari server (API barang)
        $.get(`/api/barang/${id}`, function (data) {
            const harga = parseFloat(data.harga);
            if (isNaN(harga) || harga <= 0) {
                alert("Harga barang tidak valid.");
                return;
            }
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
        const hargaDiskon = b.harga * (1 - b.diskon / 100);
        const totalHarga = hargaDiskon * b.jumlah;
        subtotal += totalHarga;

        tbody.append(`
            <tr>
                <td>${b.nama}</td>
                <td>${b.id}</td>
                <td>${b.jumlah}</td>
                <td>${b.harga.toFixed(2)}</td>
                <td>${b.diskon}%</td>
                <td>${totalHarga.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarang(${i})">Hapus</button></td>
            </tr>
            <input type="hidden" name="items[${i}][id]" value="${b.id}">
            <input type="hidden" name="items[${i}][jumlah]" value="${b.jumlah}">
            <input type="hidden" name="items[${i}][harga]" value="${b.harga}">
            <input type="hidden" name="items[${i}][diskon]" value="${b.diskon}">
        `);
    });

    $('#subtotal-text').text(`Rp ${subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}`);
}

function hapusBarang(index) {
    daftarBarang.splice(index, 1);
    renderDaftar();
}
</script>
@endsection
