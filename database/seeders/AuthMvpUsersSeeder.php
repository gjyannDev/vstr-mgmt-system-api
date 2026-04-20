<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthMvpUsersSeeder extends Seeder
{
    /**
     * Seed role-based users for MVP login testing.
     */
    public function run(): void
    {
        // Reuse existing tenant first to avoid duplicates when reseeding.
        $tenantId = DB::table('tenants')->where('name', 'Default Tenant')->value('id');

        if (! $tenantId) {
            $tenantId = DB::table('tenants')->orderBy('id')->value('id');
        }

        if (! $tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Default Tenant',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $locationId = DB::table('locations')
            ->where('tenant_id', $tenantId)
            ->where('name', 'Main Location')
            ->value('id');

        if (! $locationId) {
            $locationId = DB::table('locations')
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->value('id');
        }

        if (! $locationId) {
            $locationId = DB::table('locations')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'Main Location',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'role' => 'super_admin',
                'tenant_id' => $tenantId,
                'location_id' => null,
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
            ],
            [
                'name' => 'Kiosk User',
                'email' => 'kiosk@example.com',
                'role' => 'kiosk',
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
            ],
        ];

        foreach ($users as $payload) {
            User::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'tenant_id' => $payload['tenant_id'] ?? $tenantId,
                    'role' => $payload['role'],
                    'location_id' => $payload['location_id'] ?? null,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
