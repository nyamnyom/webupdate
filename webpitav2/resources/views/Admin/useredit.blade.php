@extends('layouts.admin_layout')

@section('title', 'Edit Akses User')

@section('content')
<h2>Edit Akses untuk User: {{ $user->username }}</h2>

<form method="POST" action="{{ url('/admin/user/'.$user->id.'/update') }}">
    @csrf
    @method('POST')

    <label>Akses:</label><br>
    @php
        $userAkses = json_decode($user->akses, true) ?? [];
    @endphp
    @foreach($aksesList as $akses)
        <input type="checkbox" name="akses[]" value="{{ $akses }}" 
            {{ in_array($akses, $userAkses) ? 'checked' : '' }}> 
        {{ ucfirst($akses) }}<br>
    @endforeach

    <br>
    <button type="submit">Update Akses</button>
</form>

<a href="{{ url('/admin/user') }}">Kembali</a>
@endsection