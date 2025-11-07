<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Facades\Tenancy;
use App\Models\SuperAdmin\Package;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Carbon\Carbon;

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
            'package_id' => 'required|integer|exists:packages,id',
        ]);
        try {

            DB::beginTransaction();
            $tenant_key = $request->tenant_key;

            // Find the package
            $package = Package::findOrFail($request->package_id);


            $tenant = Tenant::create([
                'id' => $tenant_key,
                'name' => $request->tenant_name,
                'data' => ['plan' => $package->name],
            ]);

            $tenant->domains()->create(['domain' => $tenant_key]);

            $tenant->subscriptions()->create([
                'package_id' => $package->id,
                'status' => 'active',
                'starts_at' => Carbon::now(),
                'ends_at' => $package->trial_days > 0 ? Carbon::now()->addDays($package->trial_days) : null,
            ]);

            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'tenant_id' => $tenant->id,
            ]);

            // Initialize tenancy to create the business in the tenant database
            tenancy()->initialize($tenant);

            $business = Business::create([
                'tenant_id' => $tenant->id,
                'name' => $request->tenant_name,
                'email' => $request->admin_email,
                'business_code' => strtoupper(Str::random(8)),
                'tax_number' => null,
            ]);

            tenancy()->end();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'tenant' => $tenant,
                'admin' => $admin,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
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
