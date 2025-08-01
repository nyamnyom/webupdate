<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelunasanController extends Controller
{
    
    
        public function index(Request $request)
    {   
        if (session('role') !== 'admin') {
            return redirect('/login');
        }
        $tokoList = DB::table('toko')->get();
        $selectedToko = $request->input('toko_nama');
        $selectedNota = $request->input('nokiriman');
        $notaList = null;
        $notaDetail = null;

        if ($selectedToko) {
            $toko = DB::table('toko')->where('nama_toko', $selectedToko)->first();
            $tokoId = $toko->nama_toko ?? null;

            if ($tokoId) {
                $notaList = DB::table('nota')
                    ->where('toko_id', $tokoId)
                    ->where('status', '!=', 'lunas')
                    ->get();

                if ($selectedNota) {
                    $notaDetail = DB::table('nota')
                        ->where('toko_id', $tokoId)
                        ->where('nokiriman', $selectedNota)
                        ->first();
                }
            }
        }

        return view('user.pelunasan', [
            'tokoList' => $tokoList,
            'selectedToko' => $selectedToko,
            'selectedNota' => $selectedNota,
            'notaList' => $notaList,
            'notaDetail' => $notaDetail,
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'nota_id' => 'required|integer',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $nota = DB::table('nota')
            ->where('id', $request->nota_id)
            ->where('nokiriman', $request->nokiriman)
            ->first();

        if (!$nota) {
            return redirect()->back()->with('error', 'Nota tidak ditemukan atau tidak cocok.');
        }

        if ($request->jumlah_bayar + $nota->total_dibayar > $nota->total) {
            return redirect()->back()->with('error', 'Jumlah bayar melebihi total tagihan.');
        }

        DB::beginTransaction();
        try {
            DB::table('pelunasan')->insert([
                'nota_id' => $request->nota_id,
                'nokiriman' => $request->nokiriman,
                'user_id'=> session('username'),
                'nama_toko'=> $nota->toko_id,
                'jumlah_bayar' => $request->jumlah_bayar,
                'tanggal_bayar' => $request->tanggal_bayar ?? now()->format('Y-m-d'),
                'keterangan' => $request->catatan,
                'created_at' => now(),
                
            ]);

            DB::table('nota')
                ->where('id', $request->nota_id)
                ->update([
                    'total_dibayar' => $nota->total_dibayar + $request->jumlah_bayar,
                    'status' => ($nota->total_dibayar + $request->jumlah_bayar) >= $nota->total ? 'lunas' : 'belum lunas',
                ]);

            DB::commit();
            return redirect()->route('user.pelunasan')->with('success', 'Pelunasan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pelunasan: ' . $e->getMessage());
        }
    }
}
