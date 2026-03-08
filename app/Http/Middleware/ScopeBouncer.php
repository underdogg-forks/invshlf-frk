<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class ScopeBouncer
{
    protected PermissionRegistrar $permissionRegistrar;

    public function __construct(PermissionRegistrar $permissionRegistrar)
    {
        $this->permissionRegistrar = $permissionRegistrar;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $companyHeader = $request->header('company');
        $teamId = (is_string($companyHeader) && preg_match('/^\d+$/', $companyHeader) && (int) $companyHeader > 0)
            ? (int) $companyHeader
            : ($user ? $user->companies()->first()?->id : null);

        $this->permissionRegistrar->setPermissionsTeamId($teamId);

        return $next($request);
    }
}
