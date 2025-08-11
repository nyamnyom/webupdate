<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaController extends Controller
{
    public function form()
    {
        if (session('role') !== 'user') {
            return redirect('/login');
        }

        $user = DB::table('user')->where('id', session('user_id'))->first();
        if (!$user || $user->nota != 1) {
            return redirect('/user/dashboard')->withErrors('Anda tidak memiliki akses untuk melihat stok barang.');
        }
        $sales = DB::table('sales')->orderBy('username')->get();
$toko = DB::table('toko')->get();

        return view('User.order', compact('toko', 'sales'));
    }

    public function submit(Request $request)
{
    if (session('role') !== 'user') {
        return redirect('/login');
    }

    $username = session('username') ?? 'unknown';

    // Validasi input
    $request->validate([
        'dari_toko' => 'required|exists:toko,nama_toko',
        'nokiriman' => 'required|string|max:255',
        'pengerja' => 'required|array|min:1',
        'pengerja.*' => 'string|max:100',
        'tanggal' => 'nullable|date',
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|string',
        'items.*.jumlah' => 'required|numeric|min:0.1',
        'items.*.diskon' => 'nullable|numeric|min:0|max:100',
        'items.*.harga' => 'required|numeric|min:0'
    ]);

    // Gabungkan array pengerja jadi string dipisah koma
    $pengerja = implode(', ', $request->pengerja);

    $items = $request->items;
    $total = 0;

    // Hitung total harga
    foreach ($items as $item) {
        $barang = DB::table('barang')->where('id', $item['id'])->first();
        if (!$barang) {
            return back()->withErrors("Barang dengan ID {$item['id']} tidak ditemukan.");
        }
        $diskon = isset($item['diskon']) ? floatval($item['diskon']) : 0;
        $harga_diskon = $item['harga'] * (1 - $diskon / 100);
        $total += $harga_diskon * $item['jumlah'];
    }

    // Simpan header transaksi
    $htrans_id = DB::table('nota')->insertGetId([
        'total' => $total,
        'nokiriman' => $request->nokiriman,
        'user_id' => $username,
        'toko_id' => $request->dari_toko,
        'pengerja' => $pengerja,
        'created_at' => $request->filled('tanggal') ? $request->tanggal : now(),
    ]);

    // Simpan detail dan update stok
    foreach ($items as $item) {
        $barang = DB::table('barang')->where('id', $item['id'])->first();
        $diskon = isset($item['diskon']) ? floatval($item['diskon']) : 0;
        $harga_diskon = $item['harga'] * (1 - $diskon / 100);
        $subtotal = $harga_diskon * $item['jumlah'];

        DB::table('nota_detail')->insert([
            'nota_id' => $htrans_id,
            'barang' => $barang->nama,
            'qty' => $item['jumlah'],
            'harga' => $item['harga'],
            'diskon' => $diskon,
            'subtotal' => $subtotal,
        ]);

        if ($barang->tipe === 'paket') {
            // Kurangi stok komponen paket
            $komponen = DB::table('paket_detail')->where('paket_id', $barang->id)->get();
            foreach ($komponen as $k) {
                $jumlah = $item['jumlah'] * $k->qty;
                DB::table('barang')->where('id', $k->barang_id)->decrement('stok', $jumlah);

                $komponen_barang = DB::table('barang')->where('id', $k->barang_id)->first();
                $this->logBarang(
                    $k->barang_id,
                    $komponen_barang->nama,
                    "$username order komponen dari paket {$barang->nama} x{$item['jumlah']} (jumlah $jumlah) dari toko: {$request->dari_toko}, No Kiriman: {$request->nokiriman}",
                    0,
                    $jumlah
                );
            }
        } else {
            // Barang satuan biasa
            DB::table('barang')->where('id', $item['id'])->decrement('stok', $item['jumlah']);

            $this->logBarang(
                $item['id'],
                $barang->nama,
                "$username order barang x{$item['jumlah']} dari toko: {$request->dari_toko}, No Kiriman: {$request->nokiriman}",
                0,
                $item['jumlah']
            );
        }

        // Log aktivitas user
        DB::table('user_activity_log')->insert([
            'user_id' => session('user_id'),
            'aktivitas' => "input order barang {$barang->nama} x{$item['jumlah']} dari toko: {$request->dari_toko}, No Kiriman: {$request->nokiriman}",
            'created_at' => now()
        ]);
    }

    return redirect('/user/order')->with('success', 'Order berhasil diproses');
}



    public function barangAutocomplete(Request $request)
{
    $keyword = $request->get('term');
    $keywords = array_filter(explode(' ', trim(strtolower($keyword))));

    $result = DB::table('barang')
        ->where(function ($q) use ($keywords) {
            foreach ($keywords as $word) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%$word%"]);
            }
        })
        ->limit(10)
        ->get();

    $data = [];
    foreach ($result as $r) {
        $data[] = [
            'label' => $r->nama,
            'id' => $r->id,
        ];
    }

    return response()->json($data);
}
    private function logBarang($idBarang, $namaBarang, $keterangan, $in = 0, $out = 0)
    {
        $username = session('username') ?? 'unknown';
        $stok = DB::table('barang')->where('id', $idBarang)->value('stok') ?? 0;

        DB::table('log_barang')->insert([
            'barang_id' => $idBarang,
            'nama_barang' => $namaBarang,
            'keterangan' => $keterangan,
            'stok_in' => $in,
            'stok_out' => $out,
            'stok_after' => $stok,
            'by_who' => $username,
            'created_at' => now()
        ]);
    }
}
