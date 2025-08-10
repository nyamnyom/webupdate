<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelunasanController extends Controller
{
    
    
        public function index(Request $request)
    {   
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->pelunasan != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk melihat stok barang.');
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
                    ->where('status', '!=', 'retur lunas')
                    ->where('status', '!=', 'cancel')
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
        'nota_id'      => 'required|integer',
        'jumlah_bayar' => 'required|numeric|min:1',
    ]);

    // Ambil data nota
    $nota = DB::table('nota')
        ->select('id', 'nokiriman', 'total', 'total_dibayar', 'status', 'toko_id')
        ->where('id', $request->nota_id)
        ->where('nokiriman', $request->nokiriman)
        ->first();

    if (!$nota) {
        return redirect()->back()->with('error', 'Nota tidak ditemukan atau tidak cocok.');
    }

    // Validasi kelebihan bayar
    if ($request->jumlah_bayar + $nota->total_dibayar > $nota->total) {
        return redirect()->back()->with('error', 'Jumlah bayar melebihi total tagihan.');
    }

    DB::beginTransaction();
    try {
        // Simpan pelunasan
        DB::table('pelunasan')->insert([
            'nota_id'       => $request->nota_id,
            'nokiriman'     => $request->nokiriman,
            'user_id'       => session('username'),
            'nama_toko'     => $nota->toko_id,
            'jumlah_bayar'  => $request->jumlah_bayar,
            'tanggal_bayar' => $request->tanggal_bayar ?? now()->format('Y-m-d'),
            'keterangan'    => $request->catatan,
            'created_at'    => now(),
        ]);

        // Hitung total bayar baru
        $totalBayarBaru = $nota->total_dibayar + $request->jumlah_bayar;

        // Tentukan status baru
        $statusBaru = $nota->status; // default tidak berubah
        if ($totalBayarBaru >= $nota->total) {
            $statusNota = ($nota->status); // normalisasi huruf kecil
            if ($statusNota === 'belum lunas') {
                $statusBaru = 'lunas';
            } elseif ($statusNota === 'retur belum lunas') {
                $statusBaru = 'retur lunas';
            }
        } else {
            $statusBaru = $nota->status; // tetap sama kalau belum terbayar penuh
        }

        // Update nota
        DB::table('nota')
            ->where('id', $request->nota_id)
            ->update([
                'total_dibayar' => $totalBayarBaru,
                'STATUS'        => $statusBaru,
            ]);

        DB::commit();
        return redirect()->route('user.pelunasan')->with('success', 'Pelunasan berhasil disimpan.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menyimpan pelunasan: ' . $e->getMessage());
    }
}
}
