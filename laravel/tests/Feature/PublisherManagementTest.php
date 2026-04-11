<?php

namespace Tests\Feature;

use App\Models\Publisher;
use App\Models\PublisherContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublisherManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $publisherAdmin;
    private Publisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publisher = Publisher::create([
            'publisher_name' => 'Existing Publisher',
            'publisher_address' => '台北市測試路 1 號',
            'publisher_phone' => '+886 2 1111 2222',
            'publisher_isbn_code' => '181',
            'is_active' => true,
        ]);
        PublisherContact::create([
            'publisher_id' => $this->publisher->id,
            'contact_name' => 'Contact A',
            'contact_phone' => '+886 900 111 222',
            'contact_email' => 'a@example.com',
        ]);

        $this->superAdmin = User::create([
            'username' => 'admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Super Admin',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->publisherAdmin = User::create([
            'publisher_id' => $this->publisher->id,
            'username' => 'pub_admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Pub Admin',
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);
    }

    // ── Publisher Index ────────────────────────────────────────────

    public function test_publishers_index_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers');
        $response->assertStatus(200);
        $response->assertSee('Existing Publisher');
    }

    public function test_publishers_index_excludes_inactive(): void
    {
        Publisher::create([
            'publisher_name' => 'Inactive Pub',
            'publisher_address' => 'Addr',
            'publisher_phone' => '123',
            'publisher_isbn_code' => '999',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers');
        $response->assertStatus(200);
        $response->assertSee('Existing Publisher');
        $response->assertDontSee('Inactive Pub');
    }

    public function test_publisher_admin_cannot_access_index(): void
    {
        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/publishers');
        $response->assertStatus(403);
    }

    // ── Publisher Inactive ─────────────────────────────────────────

    public function test_publishers_inactive_returns_200(): void
    {
        Publisher::create([
            'publisher_name' => 'Disabled Pub',
            'publisher_address' => 'Addr',
            'publisher_phone' => '123',
            'publisher_isbn_code' => '999',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/inactive');
        $response->assertStatus(200);
        $response->assertSee('Disabled Pub');
    }

    // ── Publisher Show ─────────────────────────────────────────────

    public function test_publisher_show_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/' . $this->publisher->id . '/detail');
        $response->assertStatus(200);
        $response->assertSee('Existing Publisher');
    }

    // ── Publisher Create ───────────────────────────────────────────

    public function test_publisher_create_page_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/new');
        $response->assertStatus(200);
    }

    // ── Publisher Store ────────────────────────────────────────────

    public function test_store_publisher_successfully(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/publishers', [
            'publisher_name' => 'New Publisher',
            'publisher_address' => '新北市測試路 5 號',
            'publisher_phone' => '+886 2 5555 6666',
            'publisher_isbn_code' => '4567',
            'contacts' => [
                ['contact_name' => 'New Contact', 'contact_phone' => '0911111111', 'contact_email' => 'new@example.com'],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('publishers', ['publisher_name' => 'New Publisher']);
        $this->assertDatabaseHas('publisher_contacts', ['contact_name' => 'New Contact']);
    }

    public function test_store_publisher_duplicate_isbn_code_fails(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/publishers', [
            'publisher_name' => 'Dup Code Pub',
            'publisher_address' => 'Addr',
            'publisher_phone' => '123',
            'publisher_isbn_code' => '181', // already taken
            'contacts' => [
                ['contact_name' => 'C', 'contact_phone' => '000', 'contact_email' => 'c@example.com'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('publisher_isbn_code');
    }

    public function test_store_publisher_validates_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/publishers', []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['publisher_name', 'publisher_address', 'publisher_phone', 'publisher_isbn_code', 'contacts']);
    }

    public function test_store_publisher_requires_at_least_one_contact(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/publishers', [
            'publisher_name' => 'No Contact Pub',
            'publisher_address' => 'Addr',
            'publisher_phone' => '123',
            'publisher_isbn_code' => '7777',
            'contacts' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('contacts');
    }

    // ── Publisher Edit ─────────────────────────────────────────────

    public function test_publisher_edit_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/' . $this->publisher->id . '/edit');
        $response->assertStatus(200);
    }

    // ── Publisher Update ───────────────────────────────────────────

    public function test_update_publisher_successfully(): void
    {
        $response = $this->actingAs($this->superAdmin)->put('/XX_module_d/publishers/' . $this->publisher->id, [
            'publisher_name' => 'Updated Name',
            'publisher_address' => 'Updated Addr',
            'publisher_phone' => '999',
            'publisher_isbn_code' => '181',
            'contacts' => [
                ['contact_name' => 'Updated Contact', 'contact_phone' => '0922222222', 'contact_email' => 'updated@example.com'],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('publishers', ['id' => $this->publisher->id, 'publisher_name' => 'Updated Name']);
    }

    public function test_update_publisher_replaces_contacts(): void
    {
        $this->actingAs($this->superAdmin)->put('/XX_module_d/publishers/' . $this->publisher->id, [
            'publisher_name' => 'Same',
            'publisher_address' => 'Same',
            'publisher_phone' => '123',
            'publisher_isbn_code' => '181',
            'contacts' => [
                ['contact_name' => 'Brand New', 'contact_phone' => '0900000000', 'contact_email' => 'brand@example.com'],
            ],
        ]);

        $this->assertDatabaseMissing('publisher_contacts', ['contact_name' => 'Contact A']);
        $this->assertDatabaseHas('publisher_contacts', ['contact_name' => 'Brand New']);
    }

    // ── Publisher Disable/Enable ───────────────────────────────────

    public function test_disable_publisher(): void
    {
        $response = $this->actingAs($this->superAdmin)->patch('/XX_module_d/publishers/' . $this->publisher->id . '/disable');
        $response->assertStatus(302);
        $this->assertFalse($this->publisher->fresh()->is_active);
    }

    public function test_enable_publisher(): void
    {
        $this->publisher->update(['is_active' => false]);

        $response = $this->actingAs($this->superAdmin)->patch('/XX_module_d/publishers/' . $this->publisher->id . '/enable');
        $response->assertStatus(302);
        $this->assertTrue($this->publisher->fresh()->is_active);
    }

    // ── Publisher Admin CRUD ───────────────────────────────────────

    public function test_admin_create_page_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/' . $this->publisher->id . '/admins/new');
        $response->assertStatus(200);
    }

    public function test_store_admin_successfully(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/publishers/' . $this->publisher->id . '/admins', [
            'username' => 'new_admin',
            'password' => 'secret',
            'display_name' => 'New Admin User',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'username' => 'new_admin',
            'publisher_id' => $this->publisher->id,
            'role' => User::ROLE_PUBLISHER_ADMIN,
        ]);
    }

    public function test_store_admin_duplicate_username_fails(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/publishers/' . $this->publisher->id . '/admins', [
            'username' => 'pub_admin', // already exists
            'password' => 'secret',
            'display_name' => 'Dup Admin',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');
    }

    public function test_admin_edit_page_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/' . $this->publisher->id . '/admins/' . $this->publisherAdmin->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_admin_edit_wrong_publisher_returns_404(): void
    {
        $otherPub = Publisher::create([
            'publisher_name' => 'Other',
            'publisher_address' => 'Addr',
            'publisher_phone' => '000',
            'publisher_isbn_code' => '8888',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/publishers/' . $otherPub->id . '/admins/' . $this->publisherAdmin->id . '/edit');
        $response->assertStatus(404);
    }

    public function test_update_admin_successfully(): void
    {
        $response = $this->actingAs($this->superAdmin)->put('/XX_module_d/publishers/' . $this->publisher->id . '/admins/' . $this->publisherAdmin->id, [
            'username' => 'updated_admin',
            'display_name' => 'Updated Name',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['id' => $this->publisherAdmin->id, 'username' => 'updated_admin']);
    }

    public function test_update_admin_with_password(): void
    {
        $response = $this->actingAs($this->superAdmin)->put('/XX_module_d/publishers/' . $this->publisher->id . '/admins/' . $this->publisherAdmin->id, [
            'username' => 'pub_admin',
            'password' => 'newpass',
            'display_name' => 'PubAdmin',
        ]);

        $response->assertStatus(302);
        $this->assertTrue(Hash::check('newpass', $this->publisherAdmin->fresh()->password));
    }

    public function test_destroy_admin(): void
    {
        $admin = User::create([
            'publisher_id' => $this->publisher->id,
            'username' => 'to_delete',
            'password' => Hash::make('1234'),
            'display_name' => 'Delete Me',
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete('/XX_module_d/publishers/' . $this->publisher->id . '/admins/' . $admin->id);
        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_destroy_admin_wrong_publisher_returns_404(): void
    {
        $otherPub = Publisher::create([
            'publisher_name' => 'Other',
            'publisher_address' => 'Addr',
            'publisher_phone' => '000',
            'publisher_isbn_code' => '8888',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete('/XX_module_d/publishers/' . $otherPub->id . '/admins/' . $this->publisherAdmin->id);
        $response->assertStatus(404);
    }
}
