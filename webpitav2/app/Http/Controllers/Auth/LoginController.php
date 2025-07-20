<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
{
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $user = DB::table('user')->where('username', $request->username)->first();

    if ($user && $request->password === $user->password) {
        Session::put('user_id', $user->id);
        Session::put('username', $user->username);
        Session::put('role', $user->ROLE);

        if ($user->ROLE === 'admin') {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/user/dashboard');
        }
    }

    return back()->withErrors(['login' => 'Username atau Password salah']);
}

}
