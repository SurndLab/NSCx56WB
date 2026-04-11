<?php

namespace Tests\Feature;

use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $publisherAdmin;
    private Publisher $activePublisher;
    private Publisher $inactivePublisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activePublisher = Publisher::create([
            'publisher_name' => 'Active Publisher',
            'publisher_address' => '台北市測試路 1 號',
            'publisher_phone' => '+886 2 1111 2222',
            'publisher_isbn_code' => '181',
            'is_active' => true,
        ]);

        $this->inactivePublisher = Publisher::create([
            'publisher_name' => 'Inactive Publisher',
            'publisher_address' => '高雄市測試路 2 號',
            'publisher_phone' => '+886 7 3333 4444',
            'publisher_isbn_code' => '999',
            'is_active' => false,
        ]);

        $this->superAdmin = User::create([
            'username' => 'admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Super Admin',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->publisherAdmin = User::create([
            'publisher_id' => $this->activePublisher->id,
            'username' => 'ws_admin',
            'password' => Hash::make('1234'),
            'display_name' => 'WS Admin',
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);
    }

    // ── Login Page ─────────────────────────────────────────────────

    public function test_login_page_returns_200(): void
    {
        $response = $this->get('/XX_module_d/login');
        $response->assertStatus(200);
    }

    public function test_login_page_redirects_if_authenticated(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/login');
        $response->assertRedirect(route('books.index'));
    }

    // ── Login Submit ───────────────────────────────────────────────

    public function test_login_success_redirects_to_books(): void
    {
        $response = $this->post('/XX_module_d/login', [
            'username' => 'admin',
            'password' => '1234',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    public function test_login_publisher_admin_success(): void
    {
        $response = $this->post('/XX_module_d/login', [
            'username' => 'ws_admin',
            'password' => '1234',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($this->publisherAdmin);
    }

    public function test_login_wrong_password(): void
    {
        $response = $this->post('/XX_module_d/login', [
            'username' => 'admin',
            'password' => 'wrong',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_wrong_username(): void
    {
        $response = $this->post('/XX_module_d/login', [
            'username' => 'nonexistent',
            'password' => '1234',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_inactive_user(): void
    {
        $user = User::create([
            'username' => 'inactive_user',
            'password' => Hash::make('1234'),
            'display_name' => 'Inactive',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);

        $response = $this->post('/XX_module_d/login', [
            'username' => 'inactive_user',
            'password' => '1234',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_publisher_admin_inactive_publisher(): void
    {
        $admin = User::create([
            'publisher_id' => $this->inactivePublisher->id,
            'username' => 'disabled_pub_admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Disabled PB Admin',
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post('/XX_module_d/login', [
            'username' => 'disabled_pub_admin',
            'password' => '1234',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->post('/XX_module_d/login', []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['username', 'password']);
    }

    // ── Logout ─────────────────────────────────────────────────────

    public function test_logout_redirects_to_login(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ── Auth Protection ────────────────────────────────────────────

    public function test_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get('/XX_module_d/books');
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_book_create_redirects(): void
    {
        $response = $this->get('/XX_module_d/books/new');
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_publishers_redirects(): void
    {
        $response = $this->get('/XX_module_d/publishers');
        $response->assertRedirect(route('login'));
    }

    // ── Super Admin Middleware ──────────────────────────────────────

    public function test_publisher_admin_cannot_access_publishers(): void
    {
        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/publishers');
        $response->assertStatus(403);
    }

    public function test_publisher_admin_cannot_access_publishers_create(): void
    {
        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/publishers/new');
        $response->assertStatus(403);
    }

    public function test_publisher_admin_cannot_access_publishers_inactive(): void
    {
        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/publishers/inactive');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_publishers(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers');
        $response->assertStatus(200);
    }
}
