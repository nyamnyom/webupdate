@extends('layouts.user_layout')

@section('title', 'Tambah Sales')

@section('content')
    <h2>Tambah Sales</h2>
    <form action="{{ route('sales.store') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Username Sales</label>
            <input type="text" name="username" class="form-control" required maxlength="20" value="{{ old('username') }}">
            @error('username')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
@endsection
