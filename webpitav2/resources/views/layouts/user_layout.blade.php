<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - @yield('title')</title>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background: #007bff;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: #0056b3;
        }

        .sidebar a.logout {
            background: #dc3545;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }

        .sidebar a.logout:hover {
            background: #a71d2a;
        }

        .content {
            margin-left: 230px;
            padding: 20px;
            min-height: 100vh;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Menu</h2>
    <a href="{{ url('/admin/menu') }}">Manajemen Barang</a>
    <a href="{{ url('/admin/penjualan') }}">Riwayat Penjualan</a>
    <a href="{{ url('/admin/pegawai') }}">Manajemen Pegawai</a>
    <a href="{{ url('/admin/history') }}">History Log</a>
    <a href="{{ url('/admin/log-barang') }}">Log Interaksi Barang</a>
    <a href="{{ url('/logout') }}" class="logout">Log Out</a>
</div>

<div class="content">
    @yield('content')
</div>

<!-- jQuery + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@yield('scripts')

</body>
</html>
