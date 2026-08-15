<?php

namespace App\Services;

use App\Models\BusinessPartner;
use App\Models\Config;
use App\Models\PurchaseOrder;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class SapServiceLayerManager
{
    protected $sap;

    public function __construct(?SapService $sap = null)
    {
        $config = Config::first();
        if ($config && $config->base_url) {
            $this->sap = $sap ?? new SapService($config);
        }
    }

    protected function ensureSapAvailable()
    {
        if (!$this->sap) {
            $config = Config::first();
            if (!$config || !$config->base_url) {
                throw new Exception("SAP Service Layer configuration is missing.");
            }
            $this->sap = new SapService($config);
        }
        return $this->sap;
    }

    /**
     * Push a Purchase Order to SAP B1 Service Layer (/b1s/v2/PurchaseOrders).
     */
    public function pushPurchaseOrder(PurchaseOrder $po): array
    {
        $sap = $this->ensureSapAvailable();

        $user = $po->created_by ? User::find($po->created_by) : auth()->user();
        if (!$user) {
            throw new Exception("User not found for Purchase Order.");
        }

        $isService = ($po->doc_type === 'dssService');
        $lines = [];

        foreach ($po->lines as $line) {
            $linePayload = [
                'ItemDescription' => $line->item_description,
                'VatGroup' => $line->tax_code ?? $po->tax_code,
            ];

            if ($isService) {
                $linePayload['AccountCode'] = $line->account_code;
                $linePayload['LineTotal'] = (float) $line->price;
            } else {
                $linePayload['ItemCode'] = $line->item_code;
                $linePayload['Quantity'] = (float) $line->quantity;
                $linePayload['UnitPrice'] = (float) $line->price;

                if (!empty($po->whs_code)) {
                    $linePayload['WarehouseCode'] = $po->whs_code;
                }
                if (!empty($line->uom_code)) {
                    $linePayload['UoMCode'] = $line->uom_code;
                }
            }

            if (!empty($line->costing_code)) $linePayload['CostingCode'] = $line->costing_code;
            if (!empty($line->costing_code2)) $linePayload['CostingCode2'] = $line->costing_code2;
            if (!empty($line->costing_code3)) $linePayload['CostingCode3'] = $line->costing_code3;
            if (!empty($line->costing_code4)) $linePayload['CostingCode4'] = $line->costing_code4;
            if (!empty($line->costing_code5)) $linePayload['CostingCode5'] = $line->costing_code5;

            // Target/Base document line linkages
            if (!empty($line->base_type)) $linePayload['BaseType'] = (int) $line->base_type;
            if (!empty($line->base_entry)) $linePayload['BaseEntry'] = (int) $line->base_entry;
            if (isset($line->base_line) && $line->base_line !== null) $linePayload['BaseLine'] = (int) $line->base_line;

            $lines[] = $linePayload;
        }

        $payload = [
            'CardCode' => $po->card_code,
            'DocDate' => $po->document_date,
            'DocDueDate' => $po->delivery_date,
            'TaxDate' => $po->posting_date,
            'Comments' => $po->comments,
            'DocType' => $po->doc_type ?? 'dssItem',
            'DocumentLines' => $lines,
        ];

        try {
            $response = $sap->post('PurchaseOrders', $payload, $user);

            if ($response->successful()) {
                $data = $response->json();
                $po->update([
                    'sap_number' => $data['DocNum'] ?? null,
                    'doc_entry' => $data['DocEntry'] ?? null,
                    'doc_num' => $data['DocNum'] ?? null,
                    'sync_status' => 'Synced',
                    'sap_status' => $data['DocumentStatus'] ?? 'Open',
                    'status' => 'open',
                    'sync_error' => null,
                ]);

                return ['success' => true, 'doc_entry' => $data['DocEntry'] ?? null, 'doc_num' => $data['DocNum'] ?? null];
            } else {
                $errorMsg = $sap->parseError($response);
                $po->update([
                    'sync_status' => 'Failed',
                    'sync_error' => $errorMsg,
                ]);
                return ['success' => false, 'message' => $errorMsg];
            }
        } catch (Exception $e) {
            $po->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchFromSap(string $endpoint, array $queryParams = []): array
    {
        try {
            $sap = $this->ensureSapAvailable();
            $user = auth()->user();
            
            $queryString = '';
            if (!empty($queryParams)) {
                $queryString = '?' . http_build_query($queryParams);
            }

            $response = $sap->get($endpoint . $queryString, $user);

            if ($response && $response->successful()) {
                return ['success' => true, 'data' => $response->json()['value'] ?? []];
            }

            $body = $response ? $response->body() : 'No response received from SAP Service Layer.';
            return ['success' => false, 'message' => $body];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
