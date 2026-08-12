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
        $query->orderBy($sortField, $sortDirection);

        $accounts = $query->paginate(20)->withQueryString();

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
        set_time_limit(300);
        $config = Config::first();
        if (!$config || !$config->base_url || !$config->database) {
            return back()->with('error', 'Configuration is missing.');
        }

        try {
            $sap = new SapService($config);
            $user = auth()->user();

            $accountsSynced = 0;
            $nextLink = '/ChartOfAccounts';

            while ($nextLink) {
                $path = $nextLink;
                if (strpos($nextLink, 'http') === 0) {
                    $parsedUrl = parse_url($nextLink);
                    $path = $parsedUrl['path'] . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
                    $path = preg_replace('/^\/b1s\/v[12]\//', '/', $path);
                }
                
                $path = ltrim($path, '/');

                try {
                    $response = $sap->get($user, $path);

                    if (isset($response['value']) && is_array($response['value'])) {
                        DB::beginTransaction();
                        foreach ($response['value'] as $acctData) {
                            $code = $acctData['Code'] ?? ($acctData['AcctCode'] ?? null);
                            if (!$code) continue;

                            $postable = ($acctData['Postable'] ?? 'tYES') === 'tYES' ? 'Postable' : 'Title';
                            $isControl = ($acctData['ControlAccount'] ?? 'tNO') === 'tYES';
                            $isCash = ($acctData['CashAccount'] ?? 'tNO') === 'tYES';
                            $isActive = ($acctData['ActiveAccount'] ?? 'tYES') === 'tYES';

                            $groupMask = (int) ($acctData['GroupMask'] ?? 1);
                            $categoryMap = [
                                1 => 'Assets',
                                2 => 'Liabilities',
                                3 => 'Capital and Reserves',
                                4 => 'Turnover',
                                5 => 'Cost of Sales',
                                6 => 'Operating Costs',
                                7 => 'Non-Operating Income & Expenditure',
                                8 => 'Taxation & Extraordinary Items'
                            ];
                            $category = $categoryMap[$groupMask] ?? 'Assets';

                            ChartOfAccount::updateOrCreate(
                                ['code' => $code],
                                [
                                    'name' => $acctData['Name'] ?? ($acctData['AcctName'] ?? null),
                                    'external_code' => $acctData['FormatCode'] ?? null,
                                    'currency' => $acctData['AcctCurrency'] ?? null,
                                    'levels' => $acctData['Levels'] ?? 1,
                                    'account_type' => $postable,
                                    'is_control_account' => $isControl,
                                    'is_cash_account' => $isCash,
                                    'is_active' => $isActive,
                                    'category' => $category,
                                    'sync_status' => 'Synced',
                                    'sap_status' => 'Created',
                                    'sync_error' => null
                                ]
                            );
                            $accountsSynced++;
                        }
                        DB::commit();
                    }

                    if (isset($response['odata.nextLink'])) {
                        $nextLink = $response['odata.nextLink'];
                    } else if (isset($response['@odata.nextLink'])) {
                        $nextLink = $response['@odata.nextLink'];
                    } else {
                        $nextLink = null;
                    }
                } catch (\Exception $pageException) {
                    if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }
                    Log::warning("COA sync page error at '{$path}': " . $pageException->getMessage());
                    break; // Finish with accounts synced up to this page
                }
            }

            SystemLog::logAction('sap', 'Synced Chart of Accounts', "Successfully synced {$accountsSynced} accounts from SAP.");

            return redirect()->route('chart-of-accounts.index')->with('success', "Successfully synced {$accountsSynced} Chart of Accounts from SAP.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("COA Sync Error: " . $e->getMessage());
            return redirect()->route('chart-of-accounts.index')->with('error', 'Error syncing COA: ' . $e->getMessage());
        }
    }
}
