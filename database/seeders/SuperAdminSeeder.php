<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::create(['name' => 'Super_Admin']);

           $user = User::firstOrCreate(
            ['email' => 'superadmin@serp.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('123123123'),
            ]
        );

        $user->assignRole($superAdmin);
        echo "✅ Super Admin seeded: superadmin@serp.com / 123123123\n";
    }
}
