<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SapService;

class PurchaseRequestController extends Controller
{
    public function create()
    {
        return view('purchase-request.create');
    }

    public function getVendors(SapService $sap)
    {
        try {
            $user = auth()->user();
            $data = $sap->get($user, "BusinessPartners?\$select=CardCode,CardName,ContactEmployees&\$filter=CardType eq 'cSupplier'");
            return response()->json(['success' => true, 'data' => $data['value'] ?? []]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSeries(SapService $sap)
    {
        try {
            $config = \App\Models\Config::first();
            if (!$config || empty($config->period_indicator)) {
                return response()->json(['success' => false, 'missing_config' => true, 'message' => 'Period Indicator is not configured.']);
            }

            $user = auth()->user();
            // 1470000113 is the Object Type for Purchase Request in SAP B1
            $data = $sap->post($user, "SeriesService_GetDocumentSeries", [
                "DocumentTypeParams" => [
                    "Document" => "1470000113"
                ]
            ]);

            $series = $data['value'] ?? [];
            $filteredSeries = array_values(array_filter($series, function($s) use ($config) {
                return isset($s['PeriodIndicator']) && $s['PeriodIndicator'] === $config->period_indicator;
            }));

            return response()->json(['success' => true, 'data' => $filteredSeries]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
