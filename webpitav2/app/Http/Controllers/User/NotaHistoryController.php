<?php 
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaHistoryController extends Controller
{
    public function index()
{
    $notas = DB::table('nota')->get();
    // dd($notas); // Debugging line, remove in production
    return view('user.historynota', compact('notas'));
}

    public function cancel($id)
{
    $username = session('username') ?? session('user') ?? 'unknown';

    $nota = DB::table('nota')->where('id', $id)->first();
    if (!$nota) {
        return redirect()->back()->with('error', 'Nota tidak ditemukan.');
    }

    $nokiriman = $nota->nokiriman;

    // Ambil semua detail nota
    $details = DB::table('nota_detail')->where('nota_id', $id)->get();

    foreach ($details as $detail) {
        $barang = DB::table('barang')->where('nama', $detail->barang)->first();

        if ($barang) {
            if ($barang->tipe == 'paket') {
                // Ambil komponen barang paket
                $komponen = DB::table('paket_detail')->where('paket_id', $barang->id)->get();

                foreach ($komponen as $isi) {
                    $brg = DB::table('barang')->where('id', $isi->barang_id)->first();
                    $jumlahKembali = $isi->qty * $detail->qty;

                    // Kembalikan stok komponen
                    DB::table('log_barang')->insert([
                        'barang_id' => $brg->id,
                        'nama_barang' => $brg->nama,
                        'keterangan' => 'cancel nota nokiriman ' . $nokiriman . ', paket ' . $barang->nama,
                        'stok_in' => $jumlahKembali,
                        'stok_after' => $brg->stok + $jumlahKembali,
                        'by_who' => $username,
                    ]);

                    DB::table('barang')->where('id', $brg->id)->update([
                        'stok' => $brg->stok + $jumlahKembali,
                    ]);
                }
            } else {
                // Barang biasa
                DB::table('log_barang')->insert([
                    'barang_id' => $barang->id,
                    'nama_barang' => $barang->nama,
                    'keterangan' => 'cancel nota nokiriman ' . $nokiriman,
                    'stok_in' => $detail->qty,
                    'stok_after' => $barang->stok + $detail->qty,
                    'by_who' => $username,
                ]);

                DB::table('barang')->where('id', $barang->id)->update([
                    'stok' => $barang->stok + $detail->qty,
                ]);
            }
        }
    }

    // Update status nota menjadi cancel
    DB::table('nota')->where('id', $id)->update(['status' => 'cancel']);

    return redirect()->back()->with('success', 'Nota berhasil dibatalkan dan stok telah dikembalikan.');
}

    public function edit($id)
{
    $nota = DB::table('nota')->where('id', $id)->first();

    $detail = DB::table('nota_detail')
        
        ->where('nota_detail.nota_id', $id)
        
        ->get();

     $barangSudahAda = $detail->pluck('barang')->toArray();

    // Ambil barang yang belum ada di nota
    $barangList = DB::table('barang')
        ->whereNotIn('nama', $barangSudahAda)
        ->get();
    // dd($nota, $detail); // Debugging line, remove in production
    return view('admin.editnota', compact('nota', 'detail', 'barangList'));
}

    public function update(Request $request, $id)
{
    $username = session('username') ?? session('user') ?? 'unknown';
    $user_id = session('user_id') ?? 0;

    $nota = DB::table('nota')->where('id', $id)->first();
    $nokiriman = $nota ? $nota->nokiriman : 'unknown';

    // Update nota utama
    DB::table('nota')->where('id', $id)->update([
        'nokiriman' => $request->nokiriman,
        'pengerja' => $request->pengerja,
        'STATUS' => $request->status,
    ]);

    // Hapus detail
    if ($request->filled('hapus_detail')) {
        foreach ($request->hapus_detail as $detailId) {
            $detail = DB::table('nota_detail')->where('id', $detailId)->first();
            if ($detail) {
                $barang = DB::table('barang')->where('nama', $detail->barang)->first();
                if ($barang) {
                    if ($barang->tipe == 'paket') {
                        $komponen = DB::table('paket_detail')->where('paket_id', $barang->id)->get();
                        foreach ($komponen as $isi) {
                            $brg = DB::table('barang')->where('id', $isi->barang_id)->first();
                            $jumlahKembali = $isi->qty * $detail->qty;
                            DB::table('log_barang')->insert([
                                'barang_id' => $brg->id,
                                'nama_barang' => $brg->nama,
                                'keterangan' => 'hapus nota nokiriman ' . $nokiriman . ', paket ' . $barang->nama,
                                'stok_in' => $jumlahKembali,
                                'stok_after' => $brg->stok + $jumlahKembali,
                                'by_who' => $username,
                            ]);
                            DB::table('barang')->where('id', $brg->id)->update([
                                'stok' => $brg->stok + $jumlahKembali,
                            ]);
                        }
                    } else {
                        DB::table('log_barang')->insert([
                            'barang_id' => $barang->id,
                            'nama_barang' => $barang->nama,
                            'keterangan' => 'hapus nota nokiriman ' . $nokiriman,
                            'stok_in' => $detail->qty,
                            'stok_after' => $barang->stok + $detail->qty,
                            'by_who' => $username,
                        ]);
                        DB::table('barang')->where('id', $barang->id)->update([
                            'stok' => $barang->stok + $detail->qty,
                        ]);
                    }
                }
            }
            DB::table('nota_detail')->where('id', $detailId)->delete();
        }
    }

    // Update detail lama
    if ($request->filled('detail_id')) {
        foreach ($request->detail_id as $detailId) {
            if (isset($request->qty[$detailId]) && isset($request->harga[$detailId])) {
                $old = DB::table('nota_detail')->where('id', $detailId)->first();
                $newQty = $request->qty[$detailId];
                $diskon = $request->diskon[$detailId] ?? 0;

                if ($old && $old->qty != $newQty) {
                    $barang = DB::table('barang')->where('nama', $old->barang)->first();
                    if ($barang) {
                        $selisih = $newQty - $old->qty;

                        if ($barang->tipe == 'paket') {
                            $komponen = DB::table('paket_detail')->where('paket_id', $barang->id)->get();
                            foreach ($komponen as $isi) {
                                $brg = DB::table('barang')->where('id', $isi->barang_id)->first();
                                $jumlah = $isi->qty * $selisih;
                                DB::table('log_barang')->insert([
                                    'barang_id' => $brg->id,
                                    'nama_barang' => $brg->nama,
                                    'keterangan' => 'edit nota nokiriman ' . $nokiriman . ', paket ' . $barang->nama,
                                    'stok_in' => $jumlah < 0 ? abs($jumlah) : 0,
                                    'stok_out' => $jumlah > 0 ? $jumlah : 0,
                                    'stok_after' => $brg->stok - $jumlah,
                                    'by_who' => $username,
                                ]);
                                DB::table('barang')->where('id', $brg->id)->update([
                                    'stok' => $brg->stok - $jumlah,
                                ]);
                            }
                        } else {
                            DB::table('log_barang')->insert([
                                'barang_id' => $barang->id,
                                'nama_barang' => $barang->nama,
                                'keterangan' => 'edit nota nokiriman ' . $nokiriman,
                                'stok_in' => $selisih < 0 ? abs($selisih) : 0,
                                'stok_out' => $selisih > 0 ? $selisih : 0,
                                'stok_after' => $barang->stok - $selisih,
                                'by_who' => $username,
                            ]);
                            DB::table('barang')->where('id', $barang->id)->update([
                                'stok' => $barang->stok - $selisih,
                            ]);
                        }
                    }
                }

                DB::table('nota_detail')->where('id', $detailId)->update([
                    'qty' => $newQty,
                    'harga' => $request->harga[$detailId],
                    'diskon' => $diskon,
                    'subtotal' => $newQty * $request->harga[$detailId] * (1 - $diskon / 100),
                ]);
            }
        }
    }

    // Tambah barang baru
    if ($request->filled('barang_id_baru')) {
        foreach ($request->barang_id_baru as $index => $barangId) {
            $qty = $request->qty_baru[$index];
            $harga = $request->harga_baru[$index];
            $diskon = $request->diskon_baru[$index] ?? 0;

            if ($barangId && $qty && $harga) {
                $barang = DB::table('barang')->where('id', $barangId)->first();
                if ($barang) {
                    DB::table('nota_detail')->insert([
                        'nota_id' => $id,
                        'barang' => $barang->nama,
                        'qty' => $qty,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'subtotal' => $qty * $harga * (1 - $diskon / 100),
                    ]);

                    if ($barang->tipe == 'paket') {
                        $komponen = DB::table('paket_detail')->where('paket_id', $barang->id)->get();
                        foreach ($komponen as $isi) {
                            $brg = DB::table('barang')->where('id', $isi->barang_id)->first();
                            $jumlah = $isi->qty * $qty;
                            DB::table('log_barang')->insert([
                                'barang_id' => $brg->id,
                                'nama_barang' => $brg->nama,
                                'keterangan' => 'edit nota nokiriman ' . $nokiriman . ', paket ' . $barang->nama,
                                'stok_out' => $jumlah,
                                'stok_after' => $brg->stok - $jumlah,
                                'by_who' => $username,
                            ]);
                            DB::table('barang')->where('id', $brg->id)->update([
                                'stok' => $brg->stok - $jumlah,
                            ]);
                        }
                    } else {
                        DB::table('log_barang')->insert([
                            'barang_id' => $barang->id,
                            'nama_barang' => $barang->nama,
                            'keterangan' => 'edit nota nokiriman ' . $nokiriman,
                            'stok_out' => $qty,
                            'stok_after' => $barang->stok - $qty,
                            'by_who' => $username,
                        ]);
                        DB::table('barang')->where('id', $barang->id)->update([
                            'stok' => $barang->stok - $qty,
                        ]);
                    }
                }
            }
        }
    }

    // Update total nota
    $totalSubtotal = DB::table('nota_detail')->where('nota_id', $id)->sum('subtotal');
    DB::table('nota')->where('id', $id)->update(['total' => $totalSubtotal]);

    // Log aktivitas
    DB::table('user_activity_log')->insert([
        'user_id' => $user_id,
        'aktivitas' => 'Update nota ID ' . $id,
    ]);

    return redirect()->route('admin.historynota')->with('success', 'Nota berhasil diperbarui.');
}


}
