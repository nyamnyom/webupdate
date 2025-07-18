@extends('layouts.admin_layout')

@section('title', 'Dashboard')

@section('content')
    <h2>Selamat datang, {{ $username }}</h2>
    <p>Ini adalah halaman dashboard admin.</p>
@endsection
