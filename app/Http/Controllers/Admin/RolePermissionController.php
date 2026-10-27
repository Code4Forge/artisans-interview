<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Roles\StoreRoleRequest;
use App\Http\Requests\Admin\Roles\UpdateRoleRequest;
use App\Policies\RolePolicy;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RolePermissionController extends Controller
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private RoleService $roleService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeRole('viewAny');

        if ($request->ajax()) {
            return DataTables::eloquent($this->roleRepository->queryAll())
                ->addIndexColumn()
                ->addColumn('action', function (Role $role): string {
                    $actions = '<div class="flex items-center justify-center gap-1.5">';

                    // Edit button
                    if (request()->user()->can('roles.edit')) {
                        $actions .= '<a href="'.route('admin.roles.edit', $role).'" title="Edit role" class="action-btn action-btn-edit">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>';
                        $actions .= '</a>';
                    }

                    // Permissions button
                    if (request()->user()->can('roles.permissions')) {
                        $actions .= '<a href="'.route('admin.roles.permissions', $role).'" title="Permissions" class="action-btn action-btn-perm">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>';
                        $actions .= '</a>';
                    }

                    // Delete button
                    if (request()->user()->can('roles.delete')) {
                        $actions .= '<form method="POST" action="'.route('admin.roles.destroy', $role).'" class="inline ajax-delete-role-form" onsubmit="return confirmDelete(event, \'This will delete the role if not assigned to any user.\')">';
                        $actions .= csrf_field();
                        $actions .= method_field('DELETE');
                        $actions .= '<button type="submit" title="Delete role" class="action-btn action-btn-delete">';
                        $actions .= '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>';
                        $actions .= '</button>';
                        $actions .= '</form>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('admin.roles.index');
    }

    public function create(): View
    {
        $this->authorizeRole('create');

        return view('admin.roles.create');
    }

    public function store(StoreRoleRequest $request): RedirectResponse|JsonResponse
    {
        $role = $this->roleService->create($request->roleAttributes());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'role' => $role,
            ]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorizeRole('update', $role);

        return view('admin.roles.edit', [
            'role' => $role,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->roleService->update($role, $request->roleAttributes());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'role' => $role->fresh(),
            ]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully.');
    }

    public function permissions(Role $role): View
    {
        $this->authorizeRole('managePermissions', $role);

        $allPermissions = Permission::orderBy('name')->pluck('name')->toArray();
        $assignedPermissions = $role->permissions()->pluck('name')->toArray();

        // Group permissions by prefix before dot (e.g. teachers.view -> Teachers)
        $grouped = [];
        foreach ($allPermissions as $permName) {
            $parts = explode('.', $permName);
            $module = ucfirst($parts[0] ?? 'General');
            if ($module === 'Roles') {
                $module = 'Role Management';
            } elseif ($module === 'Dashboard') {
                $module = 'Dashboard';
            }

            // Clean, user-friendly label without '.' sign, e.g. "Teachers Create", "Teachers Edit", "Teachers View"
            $formattedWords = array_map(function ($part) {
                return match (strtolower($part)) {
                    'update' => 'Edit',
                    default => ucfirst($part),
                };
            }, $parts);

            $label = implode(' ', $formattedWords);

            $grouped[$module][] = [
                'name' => $permName,
                'label' => $label,
            ];
        }

        return view('admin.roles.permissions', [
            'role' => $role,
            'groupedPermissions' => $grouped,
            'assignedPermissions' => $assignedPermissions,
        ]);
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->authorizeRole('managePermissions', $role);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->roleService->updatePermissions($role, $validated['permissions'] ?? []);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permissions for '.ucfirst($role->name).' updated successfully.',
            ]);
        }

        return redirect()
            ->route('admin.roles.permissions', $role)
            ->with('status', 'Permissions for '.ucfirst($role->name).' updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        $this->authorizeRole('delete', $role);

        try {
            $this->roleService->delete($role);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully.',
                ]);
            }

            return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully.');
        } catch (\Throwable $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('admin.roles.index')->with('error', $e->getMessage());
        }
    }

    private function authorizeRole(string $ability, ?Role $role = null): void
    {
        $policy = app(RolePolicy::class);
        $allowed = $role === null
            ? $policy->{$ability}(request()->user())
            : $policy->{$ability}(request()->user(), $role);

        abort_unless($allowed, 403);
    }
}
