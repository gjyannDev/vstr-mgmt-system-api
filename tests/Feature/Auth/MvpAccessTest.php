<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MvpAccessTest extends TestCase
{
  use RefreshDatabase;

  public function test_mvp2_authenticated_access_works(): void
  {
    $user = $this->createUser('admin');

    $response = $this->postJson('/api/auth/login', [
      'email' => $user->email,
      'password' => 'password123',
    ]);

    $response
      ->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.user.role', 'admin');

    $this->assertNotNull($response->json('data.user.last_login_at'));
    $this->assertNotNull($user->fresh()->last_login_at);

    $token = $response->json('data.token');

    $this->getJson('/api/auth/session-check', $this->authHeaders($token))
      ->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.role', 'admin');
  }

  public function test_mvp3_admin_can_access_admin_route(): void
  {
    $admin = $this->createUser('admin');

    $adminToken = $this->loginAndGetToken($admin->email);

    $this->assertTokenHasAbility($adminToken, 'admin:access');

    $this->getJson('/api/admin/ping', $this->authHeaders($adminToken))
      ->assertOk()
      ->assertJsonPath('data.role', 'admin');
  }

  public function test_mvp3_customer_cannot_access_admin_route(): void
  {
    $customer = $this->createUser('customer');
    $customerToken = $this->loginAndGetToken($customer->email);

    $this->assertTokenHasAbility($customerToken, 'customer:access');

    $this->getJson('/api/admin/ping', $this->authHeaders($customerToken))
      ->assertForbidden();
  }

  public function test_mvp4_location_lock_ignores_client_location_and_uses_user_location(): void
  {
    $assignedLocationId = $this->createLocation('Main Lobby');
    $otherLocationId = $this->createLocation('Annex');

    $customer = $this->createUser('customer', $assignedLocationId);
    $token = $this->loginAndGetToken($customer->email);

    $this->postJson('/api/customer/location-check', [
      'location_id' => $otherLocationId,
    ], $this->authHeaders($token))
      ->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.location_id', $assignedLocationId);
  }

  public function test_mvp4_location_lock_blocks_accounts_without_assigned_location(): void
  {
    $customer = $this->createUser('customer', null);
    $token = $this->loginAndGetToken($customer->email);

    $this->postJson('/api/customer/location-check', [
      'location_id' => 999,
    ], $this->authHeaders($token))
      ->assertForbidden()
      ->assertJsonPath('message', 'No assigned location found for this account.');
  }

  private function createUser(string $role, ?int $locationId = null): User
  {
    return User::factory()->create([
      'name' => strtoupper($role) . ' User',
      'email' => $role . '-' . uniqid() . '@example.com',
      'password' => Hash::make('password123'),
      'role' => $role,
      'location_id' => $locationId,
    ]);
  }

  private function loginAndGetToken(string $email): string
  {
    $response = $this->postJson('/api/auth/login', [
      'email' => $email,
      'password' => 'password123',
    ]);

    $response->assertOk();

    return $response->json('data.token');
  }

  private function authHeaders(string $token): array
  {
    return [
      'Authorization' => 'Bearer ' . $token,
      'Accept' => 'application/json',
    ];
  }

  private function createLocation(string $name): int
  {
    return DB::table('locations')->insertGetId([
      'name' => $name,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  private function assertTokenHasAbility(string $plainTextToken, string $ability): void
  {
    $tokenId = (int) explode('|', $plainTextToken)[0];
    $abilities = DB::table('personal_access_tokens')->where('id', $tokenId)->value('abilities');

    $this->assertNotNull($abilities);
    $this->assertContains($ability, json_decode($abilities, true, 512, JSON_THROW_ON_ERROR));
  }
}
