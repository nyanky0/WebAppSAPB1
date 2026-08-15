<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\SystemLog;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%")
                  ->orWhere('external_code', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        $sortField = $request->get('sort', 'code');
        $sortDirection = $request->get('direction', 'asc');
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $accounts = $query->paginate($perPage)->withQueryString();

        $categories = [
            'Assets', 
            'Liabilities', 
            'Capital and Reserves', 
            'Turnover', 
            'Cost of Sales', 
            'Operating Costs', 
            'Non-Operating Income & Expenditure', 
            'Taxation & Extraordinary Items'
        ];

        return view('chart-of-accounts.index', compact('accounts', 'sortField', 'sortDirection', 'categories'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncChartOfAccounts();
    }
}
