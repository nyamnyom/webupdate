<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PelunasanController extends Controller
{
public function index(Request $request)
{
    $toko_nama = $request->input('toko_nama');

    $tokoList = DB::table('toko')->pluck('nama_toko');

    $notaList = [];
    if ($toko_nama) {
        $notaList = DB::table('nota')
            ->where('toko_id', $toko_nama)
            ->where('status', '=', 'belum lunas')
            ->get();
    }

    return view('user/pelunasan', compact('tokoList', 'notaList', 'toko_nama'));
}


    public function bayar(Request $request)
{
    $nota_id = $request->input('nota_id');
    $tanggal_bayar = $request->input('tanggal_bayar') ?? date('Y-m-d');

    $nota = DB::table('nota')->where('id', $nota_id)->first();
    if (!$nota) {
        return back()->with('error', 'Nota tidak ditemukan.');
    }

    
    $user = session('username');

    DB::table('pelunasan')->insert([
        'nota_id' => $nota->id,
        'nokiriman' => $nota->nokiriman,
        'user_id' => session('username') ?? '-',
        'nama_toko' => $nota->toko_id ?? '-',
        'jumlah_bayar' => $nota->total,
        'tanggal_bayar' => $tanggal_bayar,
        'keterangan' => 'Lunas otomatis',
        'created_at' => now()
    ]);

    DB::table('nota')->where('id', $nota->id)->update([
        'status' => 'lunas',
        'updated_at' => now()
    ]);

    return back()->with('success', 'Nota berhasil dilunasi.');
}


}