<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_customer_can_be_created_and_listed(): void
    {
        $createResponse = $this->postJson('/api/customers', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '090-1234-5678',
            'company' => 'Prime Co',
            'status' => 'lead',
            'notes' => 'Initial contact',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Jane Smith')
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertDatabaseHas('customers', [
            'email' => 'jane@example.com',
        ]);

        $this->getJson('/api/customers')
            ->assertOk()
            ->assertJsonPath('data.0.email', 'jane@example.com');
    }

    public function test_customer_can_be_updated_and_deleted(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'lead',
        ]);

        $this->patchJson("/api/customers/{$customer->id}", [
            'name' => 'John Updated',
            'status' => 'active',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'John Updated')
            ->assertJsonPath('data.status', 'active');

        $this->deleteJson("/api/customers/{$customer->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
