<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
     public function index()
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->stok != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk melihat stok barang.');
        }

        $barang = DB::table('barang')->get();
        return view('user.stok', compact('barang'));
    }

    public function show($id)
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->stok != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk melihat stok barang.');
        }

        $barang = DB::table('barang')->where('id', $id)->first();

        if (!$barang) {
            return redirect('/user/stok')->withErrors('Barang tidak ditemukan.');
        }

        return view('user.stok_detail', compact('barang'));
    }

    public function createBarang()
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->stok != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk menambah barang.');
        }

        return view('user.stok_create');
    }

    public function storeBarang(Request $request)
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->stok != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk menambah barang.');
        }

        $request->validate([
            'id' => 'required|string|unique:barang,id',
            'nama' => 'required|string|max:100',
            'stok' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0'
        ]);

        DB::table('barang')->insert([
            'id' => $request->id,
            'nama' => $request->nama,
            'stok' => $request->stok,
            'harga' => $request->harga,
            'tipe' => 'barang'
        ]);

        DB::table('user_activity_log')->insert([
            'user_id' => session('user_id'),
            'aktivitas' => "User menambahkan barang baru dengan ID: {$request->id}, Nama: {$request->nama}",
            'created_at' => now()
        ]);

        $this->logBarang($request->id, $request->nama, 'User menambahkan barang baru', $request->stok, 0, $request->stok);
        return redirect('/user/stok')->with('success', 'Barang berhasil ditambahkan');
    }

    public function createPaket()
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->stok != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk menambah paket.');
        }

        return view('user.paket_create');
    }

    public function storePaket(Request $request)
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->stok != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk menambah paket.');
        }

        $request->validate([
            'Nama_Barang' => 'required',
            'harga' => 'required|numeric',
            'komponen' => 'required|array|min:1',
            'komponen.*.id' => 'required',
            'komponen.*.jumlah' => 'required|numeric|min:0.01',
        ]);

        $idPaket = 'PKT' . time();

        DB::table('barang')->insert([
            'id' => $idPaket,
            'nama' => $request->Nama_Barang,
            'harga' => $request->harga,
            'stok' => 0,
            'tipe' => 'paket'
        ]);

        foreach ($request->komponen as $komp) {
            DB::table('paket_detail')->insert([
                'paket_id' => $idPaket,
                'barang_id' => $komp['id'],
                'qty' => $komp['jumlah']
            ]);
        }

        DB::table('user_activity_log')->insert([
            'user_id' => session('user_id'),
            'aktivitas' => "User menambahkan paket baru dengan ID: {$idPaket}, Nama: {$request->Nama_Barang}",
            'created_at' => now()
        ]);
        

        return redirect('/user/stok')->with('success', 'Paket berhasil ditambahkan');
    }

    public function edit($id)
{
    if (session('role') !== 'user') return redirect('/login');

    $user = DB::table('user')->where('id', session('user_id'))->first();
    if (!$user || $user->stok != 1) {
        return redirect('/user/dashboard')->withErrors('Anda tidak punya akses stok');
    }

    $barang = DB::table('barang')->where('id', $id)->first();

    if (!$barang) {
        return redirect('/user/stok')->withErrors('Barang tidak ditemukan');
    }

    return view('user.stok_edit', compact('barang'));
}

public function update(Request $request, $id)
{
    if (session('role') !== 'user') return redirect('/login');

    $request->validate([
        'nama' => 'required|string',
        'stok' => 'required|numeric',
        'harga' => 'required|numeric',
    ]);

    $barang = DB::table('barang')->where('id', $id)->first();

    if (!$barang) {
        return redirect('/user/stok')->withErrors('Barang tidak ditemukan');
    }

    // Hitung selisih stok
    $stokLama = $barang->stok;
    $stokBaru = $request->stok;
    $selisih = $stokBaru - $stokLama;

    DB::table('barang')->where('id', $id)->update([
        'nama' => $request->nama,
        'stok' => $stokBaru,
        'harga' => $request->harga,
    ]);

    // Tentukan log masuk atau keluar
    $stok_in = $selisih > 0 ? $selisih : 0;
    $stok_out = $selisih < 0 ? abs($selisih) : 0;

    // Panggil logBarang jika ada perubahan stok
    if ($stok_in > 0 || $stok_out > 0) {
        $this->logBarang(
            $id,
            $request->nama,
            'User mengupdate stok barang',
            $stok_in,
            $stok_out,
            $stokBaru
        );
    }

    return redirect('/user/stok')->with('success', 'Barang berhasil diupdate');
}

public function destroy($id)
{
    if (session('role') !== 'user') return redirect('/login');

    $barang = DB::table('barang')->where('id', $id)->first();

    if ($barang) {
        DB::table('barang')->where('id', $id)->delete();
        $this->logBarang($id, $barang->nama, 'User menghapus barang');
    }
    
    return redirect('/user/stok')->with('success', 'Barang berhasil dihapus');
}

    public function barangSearch()
{
    if (session('role') !== 'user') {
        return response()->json([]);
    }

    $term = request('term');
    
    $barang = DB::table('barang')
        ->where('tipe', 'barang')
        ->where('nama', 'like', "%{$term}%")
        ->get();

    $result = [];
    foreach ($barang as $b) {
        $result[] = [
            'id' => $b->id,
            'value' => $b->nama
        ];
    }

    return response()->json($result);
}

private function logBarang($barangId, $namaBarang, $keterangan, $stokIn = 0, $stokOut = 0, $stokAfter = 0)
{
    DB::table('log_barang')->insert([
        'barang_id' => $barangId,
        'nama_barang' => $namaBarang,
        'keterangan' => $keterangan,
        'stok_in' => $stokIn,
        'stok_out' => $stokOut,
        'stok_after' => $stokAfter,
        'by_who' => session('username') ?? 'unknown',
        'created_at' => now()
    ]);
}


}