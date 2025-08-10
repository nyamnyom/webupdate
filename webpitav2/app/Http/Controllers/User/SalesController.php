<?php 
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index()
    {
        $sales = DB::table('sales')->get();
        return view('User.sales', compact('sales'));
    }

    public function create()
    {
        return view('User.salescreate');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string|max:20',
        ]);

        // Simpan data ke database
        DB::table('sales')->insert([
            'username' => $request->username,
            'created_at' => now(), // optional karena sudah default CURRENT_TIMESTAMP
        ]);
        DB::table('user_activity_log')->insert([
            'user_id' => session('user_id'),
            'aktivitas' => 'Menambahkan sales: ' . $request->username,
            'created_at' => now(),
        ]);
        // Redirect kembali ke daftar sales
        return redirect()->route('sales.index')->with('success', 'Sales berhasil ditambahkan.');
    }
    
}
