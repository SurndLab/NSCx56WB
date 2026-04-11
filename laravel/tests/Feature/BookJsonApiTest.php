<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\PublisherContact;
use App\Models\User;
use App\Services\IsbnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookJsonApiTest extends TestCase
{
    use RefreshDatabase;

    private IsbnService $isbnService;
    private Publisher $activePublisher;
    private Publisher $inactivePublisher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->isbnService = new IsbnService();

        // Active publisher with contacts
        $this->activePublisher = Publisher::create([
            'publisher_name' => 'Test Publisher',
            'publisher_address' => '台北市測試路 1 號',
            'publisher_phone' => '+886 2 1111 2222',
            'publisher_isbn_code' => '181',
            'is_active' => true,
        ]);
        PublisherContact::create([
            'publisher_id' => $this->activePublisher->id,
            'contact_name' => 'Contact A',
            'contact_phone' => '+886 900 000 001',
            'contact_email' => 'a@example.com',
        ]);

        // Inactive publisher
        $this->inactivePublisher = Publisher::create([
            'publisher_name' => 'Inactive Publisher',
            'publisher_address' => '高雄市測試路 2 號',
            'publisher_phone' => '+886 7 3333 4444',
            'publisher_isbn_code' => '999',
            'is_active' => false,
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

    // ── books.json index ───────────────────────────────────────────

    public function test_books_json_returns_200(): void
    {
        $response = $this->getJson('/XX_module_d/books.json');
        $response->assertStatus(200);
    }

    public function test_books_json_structure(): void
    {
        $this->createBook($this->activePublisher, '978986181001', 'Book A');

        $response = $this->getJson('/XX_module_d/books.json');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['book_name', 'book_isbn', 'book_author', 'publisher_name', 'cover_image']],
                'pagination' => ['current_page', 'total_pages', 'per_page', 'next_page_url', 'prev_page_url'],
            ]);
    }

    public function test_books_json_pagination_per_page_3(): void
    {
        // Create 5 visible books
        $this->createBook($this->activePublisher, '978986181001', 'Book 1');
        $this->createBook($this->activePublisher, '978986181018', 'Book 2');
        $this->createBook($this->activePublisher, '978986181025', 'Book 3');
        $this->createBook($this->activePublisher, '978986181032', 'Book 4');
        $this->createBook($this->activePublisher, '978986181049', 'Book 5');

        // Page 1: 3 books
        $response = $this->getJson('/XX_module_d/books.json');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(3, $data['data']);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(2, $data['pagination']['total_pages']);
        $this->assertNotNull($data['pagination']['next_page_url']);

        // Page 2: 2 books
        $response2 = $this->getJson('/XX_module_d/books.json?page=2');
        $response2->assertStatus(200);
        $this->assertCount(2, $response2->json('data'));
    }

    public function test_books_json_excludes_hidden_books(): void
    {
        $this->createBook($this->activePublisher, '978986181001', 'Visible Book');
        $this->createBook($this->activePublisher, '978986181018', 'Hidden Book', hidden: true);

        $response = $this->getJson('/XX_module_d/books.json');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Visible Book', $response->json('data.0.book_name'));
    }

    public function test_books_json_excludes_inactive_publisher_books(): void
    {
        $this->createBook($this->activePublisher, '978986181001', 'Active PB Book');
        $this->createBook($this->inactivePublisher, '978986999001', 'Inactive PB Book');

        $response = $this->getJson('/XX_module_d/books.json');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Active PB Book', $response->json('data.0.book_name'));
    }

    public function test_books_json_query_search_by_name(): void
    {
        $this->createBook($this->activePublisher, '978986181001', 'Apple Juice');
        $this->createBook($this->activePublisher, '978986181018', 'Orange Juice');

        $response = $this->getJson('/XX_module_d/books.json?query=Apple');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Apple Juice', $response->json('data.0.book_name'));
    }

    public function test_books_json_query_search_by_description(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Some Book');
        $book->update(['book_description' => 'Contains unique keyword xyz123']);

        $response = $this->getJson('/XX_module_d/books.json?query=xyz123');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_books_json_empty_result(): void
    {
        $response = $this->getJson('/XX_module_d/books.json?query=nonexistent');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    // ── books/{isbn}.json show ─────────────────────────────────────

    public function test_book_json_show_returns_200(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Test Book');

        $response = $this->getJson('/XX_module_d/books/' . $book->isbn13_hyphenated . '.json');
        $response->assertStatus(200);
    }

    public function test_book_json_show_structure(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Test Book');

        $response = $this->getJson('/XX_module_d/books/' . $book->isbn13_hyphenated . '.json');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'book_name',
                'book_description',
                'book_isbn',
                'book_author',
                'images',
                'publisher' => [
                    'publisher_name',
                    'publisher_address',
                    'publisher_phone',
                    'publisher_isbn',
                    'contacts' => [['contact_name', 'contact_phone', 'contact_email']],
                ],
            ]);
    }

    public function test_book_json_show_data_values(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Apple Juice');

        $response = $this->getJson('/XX_module_d/books/' . $book->isbn13_hyphenated . '.json');
        $response->assertStatus(200)
            ->assertJson([
                'book_name' => 'Apple Juice',
                'book_author' => 'Test Author',
                'book_isbn' => $book->isbn13_hyphenated,
                'publisher' => [
                    'publisher_name' => 'Test Publisher',
                    'publisher_isbn' => '181',
                    'contacts' => [
                        ['contact_name' => 'Contact A', 'contact_email' => 'a@example.com'],
                    ],
                ],
            ]);
    }

    public function test_book_json_show_by_digits(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Digit Book');

        $response = $this->getJson('/XX_module_d/books/' . $book->isbn13_digits . '.json');
        $response->assertStatus(200)
            ->assertJson(['book_name' => 'Digit Book']);
    }

    public function test_book_json_show_hidden_returns_404(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Hidden Book', hidden: true);

        $response = $this->getJson('/XX_module_d/books/' . $book->isbn13_hyphenated . '.json');
        $response->assertStatus(404);
    }

    public function test_book_json_show_inactive_publisher_returns_404(): void
    {
        $book = $this->createBook($this->inactivePublisher, '978986999001', 'Inactive PB Book');

        $response = $this->getJson('/XX_module_d/books/' . $book->isbn13_hyphenated . '.json');
        $response->assertStatus(404);
    }

    public function test_book_json_show_nonexistent_isbn_returns_404(): void
    {
        $response = $this->getJson('/XX_module_d/books/978-986-181-999-0.json');
        $response->assertStatus(404);
    }

    public function test_book_json_show_invalid_isbn_returns_404(): void
    {
        $response = $this->getJson('/XX_module_d/books/1234567890123.json');
        $response->assertStatus(404);
    }
}
