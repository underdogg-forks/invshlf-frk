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
        $teamId = $request->header('company')
            ? (int) $request->header('company')
            : ($user ? $user->companies()->first()?->id : null);

        if ($teamId !== null) {
            $this->permissionRegistrar->setPermissionsTeamId($teamId);
        }

        return $next($request);
    }
}
