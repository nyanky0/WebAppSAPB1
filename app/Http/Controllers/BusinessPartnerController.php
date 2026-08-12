<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Support\Facades\Log;

class BusinessPartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessPartner::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bp_code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        $sortField = $request->get('sort', 'bp_code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $businessPartners = $query->paginate(20)->withQueryString();

        return view('business-partners.index', compact('businessPartners', 'sortField', 'sortDirection'));
    }

    public function sync(Request $request)
    {
        set_time_limit(300);
        
        $config = Config::first();
        if (!$config || !$config->base_url || !$config->database) {
            return back()->with('error', 'Configuration is missing.');
        }

        try {
            $sap = new SapService($config);
            $user = auth()->user();

            $bpsSynced = 0;
            $nextLink = '/BusinessPartners?$select=CardCode,CardName,CardType,ContactEmployees';

            DB::beginTransaction();

            while ($nextLink) {
                $path = $nextLink;
                if (strpos($nextLink, 'http') === 0) {
                    $parsedUrl = parse_url($nextLink);
                    $path = $parsedUrl['path'] . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
                    $path = preg_replace('/^\/b1s\/v[12]\//', '/', $path);
                }
                
                $path = ltrim($path, '/');

                $response = $sap->get($user, $path);

                if (isset($response['value']) && is_array($response['value'])) {
                    foreach ($response['value'] as $bpData) {
                        if (!isset($bpData['CardCode'])) continue;

                        $type = 'Vendor';
                        if (isset($bpData['CardType'])) {
                            if ($bpData['CardType'] === 'cCustomer') {
                                $type = 'Customer';
                            }
                        }

                        $contactPersons = [];
                        if (isset($bpData['ContactEmployees']) && is_array($bpData['ContactEmployees'])) {
                            foreach ($bpData['ContactEmployees'] as $cp) {
                                if (isset($cp['Name'])) {
                                    $contactPersons[] = $cp['Name'];
                                }
                            }
                        }

                        $localBp = BusinessPartner::where('bp_code', $bpData['CardCode'])->first();
                        
                        if ($localBp) {
                            $localBp->update([
                                'name' => $bpData['CardName'] ?? null,
                                'type' => $type,
                                'contact_persons' => $contactPersons,
                                'sync_status' => 'Synced',
                                'sap_status' => 'Created'
                            ]);
                        } else {
                            BusinessPartner::create([
                                'bp_code' => $bpData['CardCode'],
                                'name' => $bpData['CardName'] ?? null,
                                'type' => $type,
                                'contact_persons' => $contactPersons,
                                'sync_status' => 'Synced',
                                'sap_status' => 'Created'
                            ]);
                        }
                        
                        $bpsSynced++;
                    }
                }

                if (isset($response['odata.nextLink'])) {
                    $nextLink = $response['odata.nextLink'];
                } else if (isset($response['@odata.nextLink'])) {
                    $nextLink = $response['@odata.nextLink'];
                } else {
                    $nextLink = null;
                }
            }

            DB::commit();

            SystemLog::logAction('sap', 'Synced Business Partners', "Successfully synced {$bpsSynced} Business Partners from SAP.");

            return redirect()->route('business-partners.index')->with('success', "Successfully synced {$bpsSynced} Business Partners.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BP Sync Error: " . $e->getMessage());
            SystemLog::logAction('sap', 'Sync BP Failed', $e->getMessage());
            return redirect()->route('business-partners.index')->with('error', 'An error occurred during synchronization: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('business-partners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bp_code' => 'required|string|max:50|unique:business_partners,bp_code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:Vendor,Customer',
            'contact_person_1' => 'nullable|string|max:100',
            'contact_person_2' => 'nullable|string|max:100',
        ]);

        $contactPersons = [];
        if (!empty($validated['contact_person_1'])) $contactPersons[] = $validated['contact_person_1'];
        if (!empty($validated['contact_person_2'])) $contactPersons[] = $validated['contact_person_2'];

        DB::beginTransaction();
        try {
            $bp = BusinessPartner::create([
                'bp_code' => $validated['bp_code'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'contact_persons' => $contactPersons,
                'sync_status' => 'Draft',
            ]);

            DB::commit();

            if ($request->has('instant_sync') && $request->instant_sync) {
                try {
                    $config = Config::first();
                    $sap = new SapService($config);
                    $this->pushToSap($bp, $sap);
                    return redirect()->route('business-partners.index')->with('success', 'Business Partner created and instantly synced to SAP!');
                } catch (\Exception $e) {
                    return redirect()->route('business-partners.index')->with('warning', 'Business Partner created locally but instant sync failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('business-partners.index')->with('success', 'Business Partner created successfully and saved as Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create business partner: ' . $e->getMessage())->withInput();
        }
    }

    public function pushToSap(BusinessPartner $bp, SapService $sap)
    {
        try {
            $cardType = $bp->type === 'Vendor' ? 'cSupplier' : 'cCustomer';
            
            $payload = [
                'CardCode' => $bp->bp_code,
                'CardName' => $bp->name,
                'CardType' => $cardType,
            ];

            if (!empty($bp->contact_persons)) {
                $contactEmployees = [];
                foreach ($bp->contact_persons as $cp) {
                    $contactEmployees[] = ['Name' => $cp];
                }
                $payload['ContactEmployees'] = $contactEmployees;
            }

            $response = $sap->post(auth()->user(), 'BusinessPartners', $payload);
            
            $bp->update([
                'sync_status' => 'Synced',
                'sap_status' => 'Created',
                'sync_error' => null
            ]);
            
            SystemLog::logAction('sap', 'Synced Business Partner', "BP '{$bp->bp_code}' successfully pushed to SAP.");
        } catch (\Exception $e) {
            $bp->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            SystemLog::logAction('sap', 'Sync BP Failed', "BP '{$bp->bp_code}' failed: " . $e->getMessage());
            throw $e;
        }
    }
}
