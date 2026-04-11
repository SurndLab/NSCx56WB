<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use App\Services\IsbnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    private IsbnService $isbnService;
    private User $superAdmin;
    private User $publisherAdmin;
    private User $otherPublisherAdmin;
    private Publisher $publisher1;
    private Publisher $publisher2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->isbnService = new IsbnService();

        $this->publisher1 = Publisher::create([
            'publisher_name' => 'Publisher One',
            'publisher_address' => '台北市測試路 1 號',
            'publisher_phone' => '+886 2 1111 2222',
            'publisher_isbn_code' => '5678',
            'is_active' => true,
        ]);

        $this->publisher2 = Publisher::create([
            'publisher_name' => 'Publisher Two',
            'publisher_address' => '台北市測試路 2 號',
            'publisher_phone' => '+886 2 3333 4444',
            'publisher_isbn_code' => '181',
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'username' => 'admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Super Admin',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->publisherAdmin = User::create([
            'publisher_id' => $this->publisher1->id,
            'username' => 'pub1_admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Pub1 Admin',
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);

        $this->otherPublisherAdmin = User::create([
            'publisher_id' => $this->publisher2->id,
            'username' => 'pub2_admin',
            'password' => Hash::make('1234'),
            'display_name' => 'Pub2 Admin',
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);
    }

    private function createBook(Publisher $publisher, string $isbn12, string $name, bool $hidden = false): Book
    {
        $check = $this->isbnService->computeCheckDigitFrom12($isbn12);
        $isbn13 = $isbn12 . $check;
        $hyphenated = $this->isbnService->hyphenate($isbn13, $publisher->publisher_isbn_code);

        return Book::create([
            'publisher_id' => $publisher->id,
            'book_name' => $name,
            'book_description' => "Description for {$name}",
            'book_author' => 'Test Author',
            'isbn13_digits' => $isbn13,
            'isbn13_hyphenated' => $hyphenated,
            'isbn_prefix' => '978',
            'registration_group' => '986',
            'publisher_code' => $publisher->publisher_isbn_code,
            'publication_code' => substr($isbn13, 6 + strlen($publisher->publisher_isbn_code), 12 - (6 + strlen($publisher->publisher_isbn_code))),
            'check_digit' => (string) $check,
            'is_hidden' => $hidden,
        ]);
    }

    // ── Book Index ─────────────────────────────────────────────────

    public function test_super_admin_sees_all_books(): void
    {
        $this->createBook($this->publisher1, '978986567801', 'P1 Book');
        $this->createBook($this->publisher2, '978986181001', 'P2 Book');

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/books');
        $response->assertStatus(200);
        $response->assertSee('P1 Book');
        $response->assertSee('P2 Book');
    }

    public function test_publisher_admin_sees_own_books_only(): void
    {
        $this->createBook($this->publisher1, '978986567801', 'My Book');
        $this->createBook($this->publisher2, '978986181001', 'Other Book');

        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/books');
        $response->assertStatus(200);
        $response->assertSee('My Book');
        $response->assertDontSee('Other Book');
    }

    public function test_book_index_search(): void
    {
        $this->createBook($this->publisher1, '978986567801', 'Apple Guide');
        $this->createBook($this->publisher1, '978986567818', 'Orange Guide');

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/books?query=Apple');
        $response->assertStatus(200);
        $response->assertSee('Apple Guide');
        $response->assertDontSee('Orange Guide');
    }

    // ── Book Create ────────────────────────────────────────────────

    public function test_book_create_page_returns_200(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/books/new');
        $response->assertStatus(200);
    }

    public function test_publisher_admin_create_page_shows_own_publisher(): void
    {
        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/books/new');
        $response->assertStatus(200);
        $response->assertSee('Publisher One');
        $response->assertDontSee('Publisher Two');
    }

    // ── Book Store ─────────────────────────────────────────────────

    public function test_store_book_successfully(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/books', [
            'publisher_id' => $this->publisher1->id,
            'book_name' => 'New Test Book',
            'book_description' => 'A great book',
            'book_author' => 'Author Name',
            'isbn_12' => '978986567801',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('books', ['book_name' => 'New Test Book']);
    }

    public function test_store_book_with_images(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/books', [
            'publisher_id' => $this->publisher1->id,
            'book_name' => 'Image Book',
            'book_description' => 'Book with images',
            'book_author' => 'Author',
            'isbn_12' => '978986567801',
            'images' => [
                UploadedFile::fake()->image('cover.jpg', 200, 300),
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('books', ['book_name' => 'Image Book']);
        $this->assertDatabaseCount('book_images', 1);
    }

    public function test_store_book_duplicate_isbn_fails(): void
    {
        $this->createBook($this->publisher1, '978986567801', 'Existing');

        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/books', [
            'publisher_id' => $this->publisher1->id,
            'book_name' => 'Duplicate',
            'book_description' => 'Dup desc',
            'book_author' => 'Author',
            'isbn_12' => '978986567801',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('isbn_12');
    }

    public function test_store_book_wrong_publisher_code_fails(): void
    {
        // ISBN starts with 978986181 which is for publisher2 (code 181), but we submit as publisher1 (code 5678)
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/books', [
            'publisher_id' => $this->publisher1->id,
            'book_name' => 'Wrong Code',
            'book_description' => 'Mismatch',
            'book_author' => 'Author',
            'isbn_12' => '978986181001',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('isbn_12');
    }

    public function test_publisher_admin_cannot_store_for_other_publisher(): void
    {
        $response = $this->actingAs($this->publisherAdmin)->post('/XX_module_d/books', [
            'publisher_id' => $this->publisher2->id,
            'book_name' => 'Hacked Book',
            'book_description' => 'Should fail',
            'book_author' => 'Hacker',
            'isbn_12' => '978986181001',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('publisher_id');
    }

    public function test_store_book_validates_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/XX_module_d/books', []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['publisher_id', 'book_name', 'book_description', 'book_author', 'isbn_12']);
    }

    // ── Book Show ──────────────────────────────────────────────────

    public function test_super_admin_show_any_book(): void
    {
        $book = $this->createBook($this->publisher2, '978986181001', 'Any Book');

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/books/' . $book->isbn13_hyphenated);
        $response->assertStatus(200);
        $response->assertSee('Any Book');
    }

    public function test_publisher_admin_show_own_book(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Own Book');

        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/books/' . $book->isbn13_hyphenated);
        $response->assertStatus(200);
    }

    public function test_publisher_admin_show_other_book_returns_403(): void
    {
        $book = $this->createBook($this->publisher2, '978986181001', 'Other Book');

        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/books/' . $book->isbn13_hyphenated);
        $response->assertStatus(403);
    }

    // ── Book Edit ──────────────────────────────────────────────────

    public function test_edit_page_returns_200(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Edit Book');

        $response = $this->actingAs($this->superAdmin)->get('/XX_module_d/books/' . $book->isbn13_hyphenated . '/edit');
        $response->assertStatus(200);
    }

    public function test_publisher_admin_edit_other_returns_403(): void
    {
        $book = $this->createBook($this->publisher2, '978986181001', 'Other Edit');

        $response = $this->actingAs($this->publisherAdmin)->get('/XX_module_d/books/' . $book->isbn13_hyphenated . '/edit');
        $response->assertStatus(403);
    }

    // ── Book Update ────────────────────────────────────────────────

    public function test_update_book_successfully(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Old Name');

        $response = $this->actingAs($this->superAdmin)->put('/XX_module_d/books/' . $book->id, [
            'book_name' => 'New Name',
            'book_description' => 'Updated desc',
            'book_author' => 'Updated Author',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'book_name' => 'New Name']);
    }

    public function test_publisher_admin_update_other_returns_403(): void
    {
        $book = $this->createBook($this->publisher2, '978986181001', 'Other Book');

        $response = $this->actingAs($this->publisherAdmin)->put('/XX_module_d/books/' . $book->id, [
            'book_name' => 'Hacked',
            'book_description' => 'Hacked',
            'book_author' => 'Hacker',
        ]);

        $response->assertStatus(403);
    }

    // ── Book Hide/Show ─────────────────────────────────────────────

    public function test_hide_book(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Visible Book');

        $response = $this->actingAs($this->superAdmin)->patch('/XX_module_d/books/' . $book->id . '/hide');
        $response->assertStatus(302);
        $this->assertTrue($book->fresh()->is_hidden);
    }

    public function test_show_hidden_book(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Hidden Book', hidden: true);

        $response = $this->actingAs($this->superAdmin)->patch('/XX_module_d/books/' . $book->id . '/show');
        $response->assertStatus(302);
        $this->assertFalse($book->fresh()->is_hidden);
    }

    public function test_publisher_admin_hide_other_returns_403(): void
    {
        $book = $this->createBook($this->publisher2, '978986181001', 'Other');

        $response = $this->actingAs($this->publisherAdmin)->patch('/XX_module_d/books/' . $book->id . '/hide');
        $response->assertStatus(403);
    }

    // ── Book Destroy ───────────────────────────────────────────────

    public function test_destroy_hidden_book(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Delete Me', hidden: true);

        $response = $this->actingAs($this->superAdmin)->delete('/XX_module_d/books/' . $book->id);
        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_destroy_visible_book_fails(): void
    {
        $book = $this->createBook($this->publisher1, '978986567801', 'Not Hidden');

        $response = $this->actingAs($this->superAdmin)->delete('/XX_module_d/books/' . $book->id);
        $response->assertStatus(302);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_publisher_admin_destroy_other_returns_403(): void
    {
        $book = $this->createBook($this->publisher2, '978986181001', 'Other', hidden: true);

        $response = $this->actingAs($this->publisherAdmin)->delete('/XX_module_d/books/' . $book->id);
        $response->assertStatus(403);
    }
}
