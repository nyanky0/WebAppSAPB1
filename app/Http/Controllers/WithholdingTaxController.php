<?php

namespace App\Http\Controllers;

use App\Models\WithholdingTax;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithholdingTaxController extends Controller
{
    public function index(Request $request)
    {
        $query = WithholdingTax::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%")
                  ->orWhere('category', 'ilike', "%{$search}%")
                  ->orWhere('gl_account', 'ilike', "%{$search}%");
            });
        }

        // Default to active taxes first (inactive = false)
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->where('inactive', false);
        } elseif ($status === 'inactive') {
            $query->where('inactive', true);
        }

        $sortField = $request->get('sort', 'code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $withholdingTaxes = $query->paginate($perPage)->withQueryString();

        return view('withholding-taxes.index', compact('withholdingTaxes', 'sortField', 'sortDirection', 'perPage'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncWithholdingTaxes();
    }
}
