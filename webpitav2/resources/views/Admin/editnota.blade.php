@extends('layouts.admin_layout')
@section('title', 'Edit Nota')

@section('content')
<div class="container">
    <h2>Edit Nota #{{ $nota->id }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.historynota.update', $nota->id) }}">
        @csrf

        <div class="mb-3">
            <label>No Kiriman</label>
            <input type="text" name="nokiriman" class="form-control" value="{{ $nota->nokiriman }}">
        </div>
        <div class="mb-3">
            <label>Pengerja</label>
            <input type="text" name="pengerja" class="form-control" value="{{ $nota->pengerja }}">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option {{ $nota->STATUS == 'lunas' ? 'selected' : '' }}>lunas</option>
                <option {{ $nota->STATUS == 'belum lunas' ? 'selected' : '' }}>belum lunas</option>
                <option {{ $nota->STATUS == 'retur' ? 'selected' : '' }}>retur</option>
                <option {{ $nota->STATUS == 'cancel' ? 'selected' : '' }}>cancel</option>
            </select>
        </div>

        <hr>
        <h4>Detail Barang</h4>
        <table class="table table-bordered" id="tabel-barang">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Diskon</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail as $d)
                    <tr>
                        <td>
                            {{ $d->barang }}
                            <input type="hidden" name="detail_id[]" value="{{ $d->id }}">
                            <input type="hidden" name="barang[]" value="{{ $d->barang }}">
                        </td>
                        <td><input type="number" name="qty[{{ $d->id }}]" value="{{ $d->qty }}" class="form-control" min="1" oninput="updateSubtotal(this)"></td>
                        <td><input type="number" name="harga[{{ $d->id }}]" value="{{ $d->harga }}" class="form-control" step="0.01" oninput="updateSubtotal(this)"></td>
                        <td><input type="number" name="diskon[{{ $d->id }}]" value="{{ $d->diskon }}" class="form-control" step="0.01" oninput="updateSubtotal(this)"></td>
                        <td class="subtotal">{{ number_format($d->qty * $d->harga, 2) }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this, '{{ $d->id }}')">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <input type="hidden" name="hapus_detail[]" id="hapus-detail-list">

        <button type="button" class="btn btn-sm btn-success mb-3" onclick="tambahBarang()">+ Tambah Barang</button>

        <br><br>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('admin.historynota') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script>
    const barangList = @json($barangList);
    const hapusList = [];

    function tambahBarang() {
        const tbody = document.querySelector('#tabel-barang tbody');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <select name="barang_id_baru[]" class="form-control">
                    <option value="">-- Pilih Barang --</option>
                    ${barangList.map(b => `<option value="${b.id}">${b.nama}</option>`).join('')}
                </select>
            </td>
            <td><input type="number" name="qty_baru[]" class="form-control" min="1" oninput="updateSubtotal(this)"></td>
            <td><input type="number" name="harga_baru[]" class="form-control" step="0.01" oninput="updateSubtotal(this)"></td>
            <td><input type="number" name="diskon_baru[]" class="form-control" step="0.01" oninput="updateSubtotal(this)"></td>
            <td class="subtotal">{{ number_format($d->qty * $d->harga, 2) }}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button></td>
        `;

        tbody.appendChild(row);
    }

    function hapusBaris(button, id = null) {
        const row = button.closest('tr');
        if (id) {
            hapusList.push(id);
            document.getElementById('hapus-detail-list').value = hapusList.join(',');
        }
        row.remove();
    }

    function updateSubtotal(input) {
        const row = input.closest('tr');
        const qtyInput = row.querySelector('input[name^="qty"]') || row.querySelector('input[name="qty_baru[]"]');
        const hargaInput = row.querySelector('input[name^="harga"]') || row.querySelector('input[name="harga_baru[]"]');
        const diskonInput = row.querySelector('input[name^="diskon"]');
        const subtotalCell = row.querySelector('.subtotal');

        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const diskon = parseFloat(diskonInput.value) || 0;
        const subtotal = qty * (harga - (harga * diskon/100));

        subtotalCell.textContent = subtotal.toFixed(2);
    }
</script>
@endsection
