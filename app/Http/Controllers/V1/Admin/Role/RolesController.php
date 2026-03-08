<?php

namespace App\Http\Controllers\V1\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')
            ->when($request->has('orderByField'), function ($query) use ($request) {
                return $query->orderBy($request['orderByField'], $request['orderBy']);
            })
            ->when($request->company_id, function ($query) use ($request) {
                return $query->where('team_id', $request->company_id);
            })
            ->get();

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        $this->authorize('create', Role::class);

        $role = Role::create($request->getRolePayload());

        $this->syncAbilities($request, $role);

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        $role->update($request->getRolePayload());

        $this->syncAbilities($request, $role);

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        $hasUsers = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->exists();

        if ($hasUsers) {
            return respondJson('role_attached_to_users', 'Roles Attached to user');
        }

        $role->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    private function syncAbilities(RoleRequest $request, $role)
    {
        $requestedAbilities = array_flip(array_column($request->abilities ?? [], 'ability'));
        $permissionsToSync = [];
        foreach (config('abilities.abilities') as $ability) {
            if (isset($requestedAbilities[$ability['ability']])) {
                $permission = Permission::firstOrCreate([
                    'name' => $ability['ability'],
                    'guard_name' => 'web',
                ]);
                $permissionsToSync[] = $permission->id;
            }
        }
        $role->syncPermissions($permissionsToSync);

        return true;
    }
}
