<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Dimension;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = CostCenter::query()->where('center_code', 'NOT ILIKE', 'Centr_z%');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('center_code', 'ilike', "%{$search}%")
                  ->orWhere('center_name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('dimension')) {
            $query->where('dimension_code', (int) $request->dimension);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $sortField = $request->get('sort', 'center_code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $costCenters = $query->paginate($perPage)->withQueryString();
        $dimensions = Dimension::where('is_active', true)->orderBy('dimension_code')->get();

        return view('cost-centers.index', compact('costCenters', 'dimensions', 'sortField', 'sortDirection', 'perPage'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncCostCenters();
    }
}
