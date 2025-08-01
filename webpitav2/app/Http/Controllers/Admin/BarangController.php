<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BarangController extends Controller
{
    public function index()
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        $barang = DB::table('barang')->get();
        return view('admin.barang', compact('barang'));
    }

    public function create()
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        return view('admin.create');
    }

    public function store(Request $request)
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        $request->validate([
            'id' => 'required|unique:barang,id',
            'nama' => 'required',
            'stok' => 'required|numeric',
            'harga' => 'required|numeric',
            'tipe' => 'required', // barang / paket
        ]);

        DB::table('barang')->insert([
            'id' => $request->id,
            'nama' => $request->nama,
            'stok' => $request->stok,
            'harga' => $request->harga,
            'tipe' => 'barang'
        ]);

        

        $this->logBarang($request->id, $request->nama, 'Tambah barang baru', $request->stok, 0, $request->stok);

        //log activity
        $this->logActivity("Menambahkan barang baru dengan ID: {$request->id}, Nama: {$request->nama}  ");
        
        return redirect('/admin/barang')->with('success', 'Barang berhasil ditambahkan');
    }

    public function edit($id)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    $barang = DB::table('barang')->where('id', $id)->first();

    if (!$barang) {
        return redirect('/admin/barang')->withErrors('Barang tidak ditemukan');
    }

    // Jika paket
    if ($barang->tipe === 'paket') {
        $komponen = DB::table('paket_detail')
            ->join('barang', 'paket_detail.barang_id', '=', 'barang.id')
            ->where('paket_detail.paket_id', $id)
            ->select('barang.id', 'barang.nama', 'paket_detail.qty')
            ->get();


        return view('admin.edit_paket', compact('barang', 'komponen'));
    }

    // Jika barang biasa
    return view('admin.edit', compact('barang'));
}


    public function update(Request $request, $id)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    $request->validate([
        'nama' => 'required',
        'stok' => 'required|numeric',
        'harga' => 'required|numeric',
        'tipe' => 'required',
    ]);

    // Ambil data barang lama
    $barang = DB::table('barang')->where('id', $id)->first();

    if (!$barang) {
        return redirect('/admin/barang')->withErrors('Barang tidak ditemukan');
    }

    $stok_lama = $barang->stok;
    $stok_baru = $request->stok;

    // Hitung stok masuk/keluar
    $in = 0;
    $out = 0;

    if ($stok_baru > $stok_lama) {
        $in = $stok_baru - $stok_lama;
    } elseif ($stok_baru < $stok_lama) {
        $out = $stok_lama - $stok_baru;
    }

    // Update barang
    DB::table('barang')->where('id', $id)->update([
        'nama' => $request->nama,
        'stok' => $stok_baru,
        'harga' => $request->harga,
        'tipe' => 'barang' // Bisa juga pakai $request->tipe jika tidak selalu 'barang'
    ]);

    // Catat ke log barang
    $this->logBarang($id, $request->nama, 'Barang diupdate oleh admin', $in, $out, $stok_baru);

    // Catat ke log aktivitas
    $this->logActivity("Mengupdate barang dengan ID: {$id}, Nama: {$request->nama}");

    return redirect('/admin/barang')->with('success', 'Barang berhasil diupdate');
}

    public function destroy($id)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    // Cek tipe barang
    $barang = DB::table('barang')->where('id', $id)->first();

    if (!$barang) {
        return redirect('/admin/barang')->withErrors('Barang tidak ditemukan');
    }

    // Jika tipe paket, hapus detail komponen paket dulu
    if ($barang->tipe === 'paket') {
        DB::table('paket_detail')->where('paket_id', $id)->delete();
    }

    // Hapus barang
    DB::table('barang')->where('id', $id)->delete();
    $this->logBarang($id, $barang->nama, 'Barang dihapus', 0, 0, 0);
    $this->logActivity("Menghapus barang/paket dengan ID: {$id}, Nama: {$barang->nama}");
    return redirect('/admin/barang')->with('success', 'Barang berhasil dihapus');
}



    public function barangSearch()
{
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
/// form paket 
public function formPaket()
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    return view('admin.paket_create');
}

public function storePaket(Request $request)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    $request->validate([
        'Nama_Barang' => 'required',
        'harga' => 'required|numeric',
        'komponen' => 'required|array|min:1',
        'komponen.*.id' => 'required',
        'komponen.*.jumlah' => 'required|numeric|min:0.01',
    ]);

    $idPaket = 'PKT' . time(); // atau pakai UUID/custom ID lain

    // Insert paket ke barang
    DB::table('barang')->insert([
        'id' => $idPaket,
        'nama' => $request->Nama_Barang,
        'harga' => $request->harga,
        'stok' => 0,
        'tipe' => 'paket',
    ]);

    // Insert komponen
    foreach ($request->komponen as $komp) {
        DB::table('paket_detail')->insert([
            'paket_id' => $idPaket,
            'barang_id' => $komp['id'],
            'qty' => $komp['jumlah']
        ]);
    }
    $this->logActivity("Menambahkan paket baru dengan ID: {$idPaket}, Nama: {$request->Nama_Barang}");
    return redirect('/admin/barang')->with('success', 'Paket berhasil ditambahkan');
}


public function updatePaket(Request $request, $id)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    $request->validate([
        'Nama_Barang' => 'required',
        'harga' => 'required|numeric|min:0',
        'komponen' => 'required|array|min:1',
        'komponen.*.id' => 'required|exists:barang,id',
        'komponen.*.jumlah' => 'required|numeric|min:0.01'
    ]);

    // Update data barang untuk paket
    DB::table('barang')->where('id', $id)->update([
        'nama' => $request->Nama_Barang,
        'harga' => $request->harga,
        'updated_at' => now()
    ]);

    // Hapus semua detail paket lama
    DB::table('paket_detail')->where('paket_id', $id)->delete();

    // Simpan ulang komponen paket
    foreach ($request->komponen as $komp) {
        DB::table('paket_detail')->insert([
            'paket_id' => $id,
            'barang_id' => $komp['id'],
            'qty' => $komp['jumlah']
        ]);
    }
    $this->logActivity("Mengupdate paket dengan ID: {$id}");
    return redirect('/admin/barang')->with('success', 'Paket berhasil diperbarui');
}

private function logActivity($aktivitas)
{
    DB::table('user_activity_log')->insert([
        'user_id' => session('user_id'),
        'aktivitas' => $aktivitas,
        'created_at' => now()
    ]);
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
