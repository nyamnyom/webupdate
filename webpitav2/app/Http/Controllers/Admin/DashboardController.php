<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        $username = session('username');
        return view('admin.dashboard', compact('username'));
    }
}
