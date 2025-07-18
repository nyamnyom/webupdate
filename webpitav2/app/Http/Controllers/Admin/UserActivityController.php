<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserActivityController extends Controller
{
    public function index(Request $request)
    {
        if (session('role') !== 'admin') {
            return redirect('/login');
        }

        $query = DB::table('user_activity_log')
                    ->join('user', 'user_activity_log.user_id', '=', 'user.id')
                    ->select('user_activity_log.*', 'user.username', 'user.nama')
                    ->orderBy('user_activity_log.created_at', 'desc');

        if ($request->filled('username')) {
            $query->where('user.username', 'like', '%'.$request->username.'%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('user_activity_log.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('user_activity_log.created_at', '<=', $request->end_date);
        }

        $logs = $query->get();

        return view('admin.user_activity', compact('logs'));
    }
}
