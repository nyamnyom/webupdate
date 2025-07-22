<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $toko_nama = $request->input('toko_nama');
        $nokiriman = $request->input('nokiriman');

        $tokoList = DB::table('toko')->pluck('nama_toko');
        $notaList = [];
        $notaDetail = null;
        $barangList = [];

        if ($toko_nama) {
            $notaList = DB::table('nota')
                ->where('toko_id', $toko_nama)
                ->where('status', '!=', 'retur')
                ->get();
        }

        if ($toko_nama && $nokiriman) {
            $notaDetail = DB::table('nota')
                ->where('nokiriman', $nokiriman)
                ->first();

            if ($notaDetail) {
                $barangList = DB::table('nota_detail')
                    ->select('nota_detail.*')
                    ->where('nota_detail.nota_id', $notaDetail->id)
                    ->get();
            }
        }

        return view('user.retur', compact('tokoList', 'notaList', 'toko_nama', 'nokiriman', 'notaDetail', 'barangList'));
    }

    public function simpan(Request $request)
    {
        try {
            $nota_id = $request->input('nota_id');
            $qty_retur = $request->input('qty_retur');
            $keterangan = $request->input('keterangan');

            $nota = DB::table('nota')->where('id', $nota_id)->first();
            if (!$nota) {
                return redirect()->route('user.retur')->with('error', 'Nota tidak ditemukan.');
            }

            $userId = session('user_id') ?? 0;

            $toko = DB::table('toko')->where('nama_toko', $nota->toko_id)->first();

            $total_retur = 0;
            $retur_id = DB::table('retur')->insertGetId([
                'nota_id' => $nota->id,
                'nokiriman' => $nota->nokiriman,
                'user_id' => $userId,
                'nama_toko' => $toko->nama_toko ?? '-',
                'total_retur' => 0,
                'keterangan' => $keterangan,
                'created_at' => now()
            ]);

            if ($qty_retur && is_array($qty_retur)) {
                foreach ($qty_retur as $barang_nama => $qty) {
                    if ($qty > 0) {
                        $barang = DB::table('barang')->where('nama', $barang_nama)->first();
                        $nota_detail = DB::table('nota_detail')
                            ->where('nota_id', $nota_id)
                            ->where('barang', $barang_nama)
                            ->first();

                        if ($barang && $nota_detail) {
                            $diskon = $nota_detail->diskon ?? 0;
                            $harga = $nota_detail->harga;
                            $subtotal = $qty * $harga * (1 - $diskon / 100);
                            $total_retur += $subtotal;

                            DB::table('retur_detail')->insert([
                                'retur_id' => $retur_id,
                                'barang_id' => $barang->id,
                                'qty' => $qty,
                                'harga' => $harga,
                                'subtotal' => $subtotal
                            ]);

                            DB::table('barang')->where('id', $barang->id)->increment('stok', $qty);
                        }
                    }
                }
            }

            if ($total_retur > 0) {
                DB::table('retur')->where('id', $retur_id)->update([
                    'total_retur' => $total_retur
                ]);

                DB::table('nota')->where('id', $nota_id)->update([
                    'status' => 'retur',
                    'updated_at' => now()
                ]);

                return redirect()->route('user.retur')->with('success', 'Retur berhasil disimpan.');
            } else {
                return redirect()->route('user.retur')->with('error', 'Tidak ada qty retur yang diinput.');
            }
        } catch (\Exception $e) {
            return redirect()->route('user.retur')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
