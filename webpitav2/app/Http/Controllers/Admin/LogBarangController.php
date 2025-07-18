<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogBarangController extends Controller
{
    public function index(Request $request)
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $namaBarang = $request->nama_barang;

        // Query utama log barang
        $logsQuery = DB::table('log_barang');

        if ($startDate) {
            $logsQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $logsQuery->whereDate('created_at', '<=', $endDate);
        }

        if ($namaBarang) {
            $logsQuery->where('nama_barang', 'like', '%' . $namaBarang . '%');
        }

        $logsQuery->orderBy('created_at', 'asc');

        $logs = $logsQuery->get();

        // Ambil stok sebelumnya (sebelum startDate), jika ada
        $stokSebelumnya = [];
        if ($startDate) {
            $stokLogs = DB::table('log_barang')
                ->select('barang_id', 'nama_barang', DB::raw('MAX(created_at) as latest_time'))
                ->whereDate('created_at', '<', $startDate);

            if ($namaBarang) {
                $stokLogs->where('nama_barang', 'like', '%' . $namaBarang . '%');
            }

            $stokLogs = $stokLogs->groupBy('barang_id', 'nama_barang')->get();

            foreach ($stokLogs as $log) {
                $last = DB::table('log_barang')
                    ->where('barang_id', $log->barang_id)
                    ->where('created_at', $log->latest_time)
                    ->first();

                if ($last) {
                    $stokSebelumnya[] = [
                        'barang_id' => $last->barang_id,
                        'nama_barang' => $last->nama_barang,
                        'stok_after' => $last->stok_after
                    ];
                }
            }
        }

        return view('admin.log_barang', compact('logs', 'stokSebelumnya'));
    }

}
