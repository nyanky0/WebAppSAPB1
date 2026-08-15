<?php

namespace App\Http\Controllers;

use App\Models\Dimension;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DimensionController extends Controller
{
    public function index(Request $request)
    {
        $query = Dimension::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("CAST(dimension_code AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhere('dimension_name', 'ilike', "%{$search}%");
            });
        }

        $sortField = $request->get('sort', 'dimension_code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $dimensions = $query->paginate($perPage)->withQueryString();

        return view('dimensions.index', compact('dimensions', 'sortField', 'sortDirection', 'perPage'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncDimensions();
    }
}
