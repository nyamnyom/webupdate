<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('user')->where('role', 'user')->get();
        return view('admin.user', compact('users'));
    }

    public function create()
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        return view('admin.usercreate');
    }
public function store(Request $request)
{
    $data = [
        'nama' => $request->nama,
        'username' => $request->username,
        'password' => $request->password,
        'role' => 'user',
        'nota' => $request->has('nota') ? 1 : 0,
        'retur' => $request->has('retur') ? 1 : 0,
        'stok' => $request->has('stok') ? 1 : 0,
        'pelunasan' => $request->has('pelunasan') ? 1 : 0
    ];

    try {
        DB::table('user')->insert($data);
    } catch (\Exception $e) {
        dd('DB ERROR: '.$e->getMessage());
    }

    return redirect('/admin/user')->with('success', 'User berhasil ditambahkan');
}

public function edit($id)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    $user = DB::table('user')->where('id', $id)->first();
    if (!$user) {
        return redirect('/admin/user')->withErrors('User tidak ditemukan');
    }

    return view('admin.useredit', compact('user'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'nama' => 'required|string|max:100',
        'password' => 'nullable|string|min:5',
    ]);

    $update = [
        'nama' => $request->nama,
        'nota' => $request->has('nota') ? 1 : 0,
        'retur' => $request->has('retur') ? 1 : 0,
        'stok' => $request->has('stok') ? 1 : 0,
        'pelunasan' => $request->has('pelunasan') ? 1 : 0
    ];

    if ($request->filled('password')) {
        $update['password'] = $request->password;
    }

    DB::table('user')->where('id', $id)->update($update);

    return redirect('/admin/user')->with('success', 'User berhasil diupdate');
}


public function destroy($id)
{
    if (session('role') !== 'admin') {
        return redirect('/login');
    }

    DB::table('user')->where('id', $id)->delete();
    
    return redirect('/admin/user')->with('success', 'User berhasil dihapus');
}

}
