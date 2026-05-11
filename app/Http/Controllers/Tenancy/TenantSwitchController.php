<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSwitchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = Auth::user();

        // Security: Ensure user is associated with this tenant
        // Unless they are a Platform Super Admin (if you have that role)
        if (! $user->tenants->contains($tenant)) {
            abort(403, 'You do not have access to this organization.');
        }

        // Get the tenant's domain
        $domain = $tenant->domains()->first();

        if (! $domain) {
            abort(404, 'Organization domain not found.');
        }

        // Redirect to the tenant domain
        // We use absolute URL to ensure we cross domains if necessary
        $port = $request->getPort();
        $host = $domain->domain;

        if ($port && ! in_array($port, [80, 443], true)) {
            $host .= ':' . $port;
        }

        $url = $request->getScheme() . '://' . $host . '/dashboard';

        return redirect($url);
    }
}
