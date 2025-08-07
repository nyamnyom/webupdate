@extends('layouts.admin_layout')

@section('title', 'Pelunasan Nota')

@section('content')
<div class="container-fluid">
    <h2>Pelunasan Nota</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ url('/admin/pelunasan') }}">
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
     {{-- Form Pelunasan --}}
    @if($notaDetail)
        <form method="POST" action="{{ route('admin.pelunasan.simpan') }}">
            @csrf
            <input type="hidden" name="nota_id" value="{{ $notaDetail->id }}">
            <input type="hidden" name="nokiriman" value="{{ $notaDetail->nokiriman }}">

            <div class="mb-3">
                <label>Total Nota:</label>
                <input type="text" class="form-control" value="Rp {{ number_format($notaDetail->total, 0, ',', '.') }}" readonly>
            </div>

            <div class="mb-3">
                <label>Total Dibayar:</label>
                <input type="text" class="form-control" value="Rp {{ number_format($notaDetail->total_dibayar, 0, ',', '.') }}" readonly>
            </div>

            <div class="mb-3">
                <label>Jumlah Bayar Sekarang:</label>
                <input type="number" class="form-control" name="jumlah_bayar" required min="0" >
            </div>

            <div class="mb-3">
                <label>Tanggal Bayar:</label>
                <input type="date" class="form-control" name="tanggal_bayar" value="{{ date('Y-m-d') }}">
            </div>

            

            <div class="mb-3">
                <label>Catatan:</label>
                <textarea name="catatan" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Pelunasan</button>
        </form>
    @endif

   
</div>
@endsection
