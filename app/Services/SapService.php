<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class SapService
{
    protected $baseUrl;
    protected $companyDb;

    public function __construct(Config $config = null)
    {
        $config = $config ?? Config::first();
        if (!$config || !$config->base_url) {
            throw new Exception("SAP Service Layer configuration is missing. Please update it in the Config module.");
        }
        $this->baseUrl = rtrim($config->base_url, '/');
        $this->companyDb = $config->database;
    }

    protected function logDebug($user, $method, $url, $body, $response)
    {
        $log = [
            'method' => $method,
            'url' => $url,
            'database' => $this->companyDb,
            'body' => $body ? json_encode($body, JSON_PRETTY_PRINT) : null,
            'response' => $response->body(),
            'status' => $response->status()
        ];
        session()->push('sap_debug_logs', $log);
    }

    public function login($user)
    {
        if (!$user->sap_user || !$user->sap_password) {
            throw new Exception("SAP credentials not configured for this user. Please update your profile.");
        }

        // Cache session based on user ID to avoid logging in on every request (expires in 25 mins)
        $cacheKey = 'sap_session_' . $user->uid7;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $parsed = parse_url($this->baseUrl);
        $hostHeader = 'localhost' . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

        $response = Http::withoutVerifying()
            ->withHeaders(['Host' => $hostHeader])
            ->post("{$this->baseUrl}/Login", [
                'UserName' => $user->sap_user,
                'Password' => $user->sap_password,
                'CompanyDB' => $this->companyDb,
            ]);

        if ($response->successful()) {
            $sessionId = $response->json('SessionId');
            Cache::put($cacheKey, $sessionId, now()->addMinutes(25));
            return $sessionId;
        }

        throw new Exception("SAP Login Failed: " . $response->body());
    }

    public function get($user, $endpoint)
    {
        $sessionId = $this->login($user);

        $parsed = parse_url($this->baseUrl);
        $hostHeader = 'localhost' . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Host' => $hostHeader,
                'Cookie' => "B1SESSION=$sessionId"
            ])
            ->get("{$this->baseUrl}/$endpoint");

        // Handle Session Timeout / Unauthorized
        if ($response->status() === 401) {
            Cache::forget('sap_session_' . $user->uid7);
            $sessionId = $this->login($user);
            
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Host' => $hostHeader,
                    'Cookie' => "B1SESSION=$sessionId"
                ])
                ->get("{$this->baseUrl}/$endpoint");
        }

        $this->logDebug($user, 'GET', "{$this->baseUrl}/$endpoint", null, $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception("SAP Request Failed: " . $response->body());
    }

    public function post($user, $endpoint, $data = [])
    {
        $sessionId = $this->login($user);

        $parsed = parse_url($this->baseUrl);
        $hostHeader = 'localhost' . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Host' => $hostHeader,
                'Cookie' => "B1SESSION=$sessionId"
            ])
            ->post("{$this->baseUrl}/$endpoint", $data);

        // Handle Session Timeout / Unauthorized
        if ($response->status() === 401) {
            Cache::forget('sap_session_' . $user->uid7);
            $sessionId = $this->login($user);
            
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Host' => $hostHeader,
                    'Cookie' => "B1SESSION=$sessionId"
                ])
                ->post("{$this->baseUrl}/$endpoint", $data);
        }

        $this->logDebug($user, 'POST', "{$this->baseUrl}/$endpoint", $data, $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception("SAP Request Failed: " . $response->body());
    }

    public function getDatabases()
    {
        $parsed = parse_url($this->baseUrl);
        $hostHeader = 'localhost' . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

        // 1. Try POST with Host header (standard for SAP B1 Service Layer CompanyService_GetCompanyList)
        $response = Http::withoutVerifying()
            ->withHeaders(['Host' => $hostHeader])
            ->post("{$this->baseUrl}/CompanyService_GetCompanyList", []);

        // 2. Try POST without Host header
        if (!$response->successful()) {
            $response = Http::withoutVerifying()
                ->post("{$this->baseUrl}/CompanyService_GetCompanyList", []);
        }

        // 3. Try GET with Host header
        if (!$response->successful()) {
            $response = Http::withoutVerifying()
                ->withHeaders(['Host' => $hostHeader])
                ->get("{$this->baseUrl}/CompanyService_GetCompanyList");
        }

        // 4. Try GET without Host header
        if (!$response->successful()) {
            $response = Http::withoutVerifying()
                ->get("{$this->baseUrl}/CompanyService_GetCompanyList");
        }

        $this->logDebug(auth()->user(), 'POST/GET', "{$this->baseUrl}/CompanyService_GetCompanyList", null, $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception("SAP Request Failed (" . $response->status() . "): " . $response->body());
    }
}
