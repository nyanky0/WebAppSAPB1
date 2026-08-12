<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;

class EnsureConfigIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        // Avoid infinite redirects by excluding routes
        $excludedRoutes = [
            'config.index',
            'config.update',
            'config.updatePersonal',
            'config.missing',
            'logout',
            'api.config.fetch-period',
            'api.config.fetch-databases',
        ];

        if ($request->route() && in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        $config = Config::first();

        // Check if config is completely filled
        if (!$config || empty($config->base_url) || empty($config->database) || empty($config->period_indicator)) {
            $user = Auth::user();
            
            // Check if user has permission to configure
            $permissions = $user->role?->permissions ?? [];
            if (in_array('Administrator.Config', $permissions)) {
                return redirect()->route('config.index')->with('error', 'You need to config first before using web app.');
            } else {
                return redirect()->route('config.missing');
            }
        }

        return $next($request);
    }
}
