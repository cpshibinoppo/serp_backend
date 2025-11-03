<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Facades\Tenancy;

class TenantRegistrationController extends Controller
{
    /**
     * Super Admin → Create Tenant and its Admin User
     */
    public function registerTenant(Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|string',
            'tenant_key' => 'required|string|unique:tenants,id',
            'admin_name' => 'required|string',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|min:6',
        ]);

        $tenant_key = $request->tenant_key;

        // 1️⃣ Create Tenant
        $tenant = Tenant::create([
            'id' => $tenant_key,
            'name' => $request->tenant_name,
            'data' => ['plan' => 'basic'],
        ]);

        $tenant->domains()->create(['domain' => $tenant_key]);

        // 2️⃣ Create Admin User
        $admin = User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role' => 'admin',
            'tenant_id' => $tenant->id,
        ]);

        return response()->json([
            'status' => 'success',
            'tenant' => $tenant,
            'admin' => $admin,
        ]);
    }

    /**
     * Admin → Create Staff User inside Tenant
     */
    public function createStaff(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
            'role'     => 'required|string',
        ]);

        $tenant = Tenancy::getTenant();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant context not found'], 400);
        }

        $staff = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $staff->assignRole($request->role); // e.g. Cashier, Sales

        return response()->json([
            'status'  => 'success',
            'message' => 'Staff created successfully',
            'staff'   => $staff,
        ]);
    }
}
