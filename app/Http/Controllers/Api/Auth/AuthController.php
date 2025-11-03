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
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->email;
        $password = $request->password;

        $superAdmin = User::where('email', $email)->first();

        if ($superAdmin && Hash::check($password, $superAdmin->password) && $superAdmin->hasRole('super_admin')) {
            $token = $superAdmin->createToken('superadmin-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'type' => 'super_admin',
                'user' => $superAdmin,
                'token' => $token,
            ]);
        }

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $foundUser = null;

            $tenant->run(function () use (&$foundUser, $email) {
                $foundUser = User::where('email', $email)->first(); // ✅ No tenant_id filter
            });

            if ($foundUser && Hash::check($password, $foundUser->password)) {
                $token = $foundUser->createToken('tenant-token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'type' => $foundUser->getRoleNames()->first() ?? 'staff',
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'user' => $foundUser,
                    'token' => $token,
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
