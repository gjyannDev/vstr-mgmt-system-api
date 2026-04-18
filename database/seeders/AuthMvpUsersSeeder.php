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
    $tenantId = DB::table('tenants')->insertGetId([
      'name' => 'Default Tenant',
      'status' => 'active',
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $locationId = DB::table('locations')->insertGetId([
      'tenant_id' => $tenantId,
      'name' => 'Main Location',
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $users = [
      [
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'role' => 'super_admin',
      ],
      [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => 'admin',
      ],
      [
        'name' => 'Kiosk User',
        'email' => 'kiosk@example.com',
        'role' => 'kiosk',
        'location_id' => $locationId,
      ],
    ];

    foreach ($users as $payload) {
      User::updateOrCreate(
        ['email' => $payload['email']],
        [
          'name' => $payload['name'],
          'role' => $payload['role'],
          'location_id' => $payload['location_id'] ?? null,
          'password' => Hash::make('password123'),
          'email_verified_at' => now(),
        ]
      );
    }
  }
}
