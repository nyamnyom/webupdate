<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(Request $request)
{   
    
    if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->retur != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk melihat stok barang.');
        }
    
    $tokoList = DB::table('toko')->get();
    $selectedToko = $request->input('toko_nama');
    $selectedNota = $request->input('nokiriman');

    // Ambil toko_id dari nama toko
    $toko = DB::table('toko')->where('nama_toko', $selectedToko)->first();
    $tokoId = $toko->nama_toko ?? null;

    // Hanya ambil nota yang status-nya 'belum lunas'
    $notaList = $tokoId
        ? DB::table('nota')
            ->where('toko_id', $tokoId)
            ->where('status', 'belum lunas')
            ->get()
        : [];

    $notaBarang = [];
    if ($selectedNota) {
        $nota = DB::table('nota')->where('nokiriman', $selectedNota)->first();
        if ($nota) {
            $notaBarang = DB::table('nota_detail')
                ->where('nota_id', $nota->id)
                ->get();
        }
    }

    $semuaBarang = DB::table('barang')->get();

    return view('user.retur', compact(
        'tokoList',
        'notaList',
        'notaBarang',
        'semuaBarang',
        'selectedToko',
        'selectedNota'
    ));
}


    public function submit(Request $request)
{
    $request->validate([
        'toko_nama' => 'required',
        'nokiriman' => 'required',
        'barang_id.*' => 'required',
        'qty.*' => 'required|numeric|min:0.01',
    ]);

    $nota = DB::table('nota')->where('nokiriman', $request->nokiriman)->first();
    if (!$nota) {
        return back()->with('error', 'Nota tidak ditemukan.');
    }

    $returId = DB::table('retur')->insertGetId([
        'nota_id' => $nota->id,
        'nokiriman' => $request->nokiriman,
        'user_id' => session('username') ?? 'unknown',
        'nama_toko' => $request->toko_nama,
        'total_retur' => 0,
        'keterangan' => $request->keterangan ?? null,
        'created_at' => now(),
    ]);

    $totalRetur = 0;
    foreach ($request->barang_id as $i => $barang_id) {
       $qty = floatval($request->qty[$i]);

$barang = DB::table('barang')->where('id', $barang_id)->first();
if (!$barang) continue;

$isTambahan = isset($request->is_tambahan[$i]) && $request->is_tambahan[$i] == '1' ? 1 : 0;

if (!$isTambahan) {
    $notaDetail = DB::table('nota_detail')
        ->where('nota_id', $nota->id)
        ->where('barang', $barang->nama)
        ->first();

    $harga = $notaDetail
        ? ($notaDetail->harga - ($notaDetail->diskon / 100 * $notaDetail->harga ?? 0))
        : $barang->harga;
} else {
    $harga = $barang->harga;
}

$subtotal = $qty * $harga;

// Simpan ke retur_detail (selalu simpan nama paket/barang utama)
DB::table('retur_detail')->insert([
    'retur_id' => $returId,
    'barang_id' => $barang_id,
    'qty' => $qty,
    'harga' => $harga,
    'subtotal' => $subtotal,
    'is_tambahan' => $isTambahan,
]);

$totalRetur += $subtotal;

// ⬇️ Penanganan barang & log
if ($barang->tipe === 'barang') {
    // Barang biasa
    $stokSebelum = $barang->stok;
    DB::table('barang')->where('id', $barang_id)->increment('stok', $qty);

    DB::table('log_barang')->insert([
        'barang_id' => $barang_id,
        'nama_barang' => $barang->nama,
        'keterangan' => 'Retur Barang',
        'stok_in' => $qty,
        'stok_out' => 0,
        'stok_after' => $stokSebelum + $qty,
        'by_who' => session('username') ?? 'unknown',
        'created_at' => now(),
    ]);
} elseif ($barang->tipe === 'paket') {
    // Barang paket → update tiap komponen
    $komponen = DB::table('paket_detail')->where('paket_id', $barang->id)->get();

    foreach ($komponen as $item) {
        $komponenBarang = DB::table('barang')->where('id', $item->barang_id)->first();
        if (!$komponenBarang) continue;

        $qtyRetur = $qty * $item->qty; // total qty komponen

        DB::table('barang')->where('id', $komponenBarang->id)->increment('stok', $qtyRetur);

        DB::table('log_barang')->insert([
            'barang_id' => $komponenBarang->id,
            'nama_barang' => $komponenBarang->nama,
            'keterangan' => 'Retur Barang dari Paket: ' . $barang->nama,
            'stok_in' => $qtyRetur,
            'stok_out' => 0,
            'stok_after' => $komponenBarang->stok + $qtyRetur,
            'by_who' => session('username') ?? 'unknown',
            'created_at' => now(),
        ]);
    }
}
    }


    // Update total retur
    DB::table('retur')->where('id', $returId)->update(['total_retur' => $totalRetur]);

    // ✅ Kurangi total_dibayar pada nota
    DB::table('nota')->where('id', $nota->id)->update([
        'total_dibayar' => DB::raw("GREATEST(0, total_dibayar + {$totalRetur})")
    ]);

    // ✅ Update status nota jadi 'retur'
    DB::table('nota')->where('id', $nota->id)->update(['status' => 'retur']);

    return redirect()->route('user.retur', [
        'toko_nama' => $request->toko_nama,
        'nokiriman' => $request->nokiriman
    ])->with('success', 'Retur berhasil disimpan!');
}

}
