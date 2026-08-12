<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::with('user')->latest();

        if ($request->has('category') && $request->category != 'All') {
            $query->where('category', strtolower($request->category));
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'ilike', "%{$search}%")
                  ->orWhere('details', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'ilike', "%{$search}%")
                        ->orWhere('username', 'ilike', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('logs.index', compact('logs'));
    }
}
