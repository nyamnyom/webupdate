<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index()
    {
        $sales = DB::table('sales')->get();
        return view('admin.sales', compact('sales'));
    }

    public function create()
    {
        return view('admin.salesCreate');
    }

    public function store(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:20',
    ]);

    DB::table('sales')->insert([
        'username' => $request->username,
        'created_at' => now(),
    ]);

    DB::table('user_activity_log')->insert([
        'user_id' => session('user_id'),
        'aktivitas' => 'Menambahkan sales: ' . $request->username,
        'created_at' => now(),
    ]);

    return redirect()->route('admin.sales.index')->with('success', 'Sales berhasil ditambahkan.');
}

public function destroy($id)
{
    $name = DB::table('sales')->where('id', $id)->value('username');

    if ($name) {
        DB::table('sales')->where('id', $id)->delete();

        DB::table('user_activity_log')->insert([
            'user_id' => session('user_id'),
            'aktivitas' => 'Menghapus sales: ' . $name,
            'created_at' => now(),
        ]);
    }

    return redirect()->route('admin.sales.index')->with('success', 'Sales berhasil dihapus.');
}
    
}
