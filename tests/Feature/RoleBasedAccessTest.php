<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $editor;
    protected $moderator;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
        $this->moderator = User::factory()->create(['role' => 'moderator']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function admin_can_access_all_endpoints()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/products');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function editor_can_create_products()
    {
        $category = Category::factory()->create();
        
        $response = $this->actingAs($this->editor)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 100000,
                'stock' => 10,
                'category_id' => $category->id,
                'images' => [\Illuminate\Http\UploadedFile::fake()->image('test.jpg')]
            ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function user_cannot_create_products()
    {
        $category = Category::factory()->create();
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 100000,
                'category_id' => $category->id,
                'image' => 'test.jpg'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function moderator_can_view_all_transactions()
    {
        Transaction::factory()->count(3)->create();
        
        $response = $this->actingAs($this->moderator)
            ->getJson('/api/transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_view_all_transactions()
    {
        Transaction::factory()->count(3)->create();
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_view_own_transactions()
    {
        $transaction = Transaction::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/my-transactions');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_manage_users()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_manage_users()
    {
        $response = $this->actingAs($this->editor)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_update_own_profile()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/users/{$this->user->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_update_other_user_profile()
    {
        $otherUser = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/users/{$otherUser->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_user()
    {
        $otherUser = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($this->admin)
            ->putJson("/api/users/{$otherUser->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_and_moderator_can_view_audit_trails()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/audit-trails');
        
        $response->assertStatus(200);

        $response = $this->actingAs($this->moderator)
            ->getJson('/api/audit-trails');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_view_audit_trails()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/audit-trails');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_view_own_audit_trails()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/my-audit-trails');

        $response->assertStatus(200);
    }
}