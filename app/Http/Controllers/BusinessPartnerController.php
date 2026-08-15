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

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $businessPartners = $query->paginate($perPage)->withQueryString();

        return view('business-partners.index', compact('businessPartners', 'sortField', 'sortDirection', 'perPage'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncBusinessPartners();
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
