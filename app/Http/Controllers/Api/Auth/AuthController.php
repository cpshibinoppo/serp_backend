<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Facades\Tenancy;

class AuthController extends Controller
{
    public function superAdminLogin(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password) && $user->hasRole('Super Admin')) {
            $token = $user->createToken('superadmin-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'type'   => 'super_admin',
                'user'   => $user,
                'token'  => $token,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Invalid super admin credentials',
        ], 401);
    }

    public function tenantLogin(Request $request)
    {
        $validated = $request->validate([
            'tenant'   => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 🔹 1. Find tenant by identifier
        $tenant = Tenant::where('id', $validated['tenant'])
            ->orWhere('name', $validated['tenant'])
            ->first();

        if (!$tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid tenant identifier'
            ], 404);
        }

        // 🔹 2. Initialize tenant context
        Tenancy::initialize($tenant);

        // 🔹 3. Authenticate user in that tenant DB
        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            $role  = $user->getRoleNames()->first() ?? 'staff';
            $token = $user->createToken('tenant-token')->plainTextToken;

            Tenancy::end();

            return response()->json([
                'status'      => 'success',
                'type'        => $role,
                'tenant_id'   => $tenant->id,
                'tenant_name' => $tenant->name,
                'user'        => $user,
                'token'       => $token,
            ]);
        }

        Tenancy::end();

        return response()->json([
            'status'  => 'error',
            'message' => 'Invalid email or password'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
