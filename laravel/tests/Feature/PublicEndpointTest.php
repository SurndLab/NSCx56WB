<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\PublisherContact;
use App\Services\IsbnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEndpointTest extends TestCase
{
    use RefreshDatabase;

    private IsbnService $isbnService;
    private Publisher $activePublisher;
    private Publisher $inactivePublisher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->isbnService = new IsbnService();

        $this->activePublisher = Publisher::create([
            'publisher_name' => 'Active Publisher',
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

    // ── ISBN Validation Page ───────────────────────────────────────

    public function test_isbn_validation_page_returns_200(): void
    {
        $response = $this->get('/XX_module_d/isbn-validate');
        $response->assertStatus(200);
    }

    public function test_isbn_validation_submit_all_valid(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Book A');

        $response = $this->post('/XX_module_d/isbn-validate', [
            'isbns' => $book->isbn13_digits,
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('allValid', true);
        $response->assertViewHas('results');
    }

    public function test_isbn_validation_submit_invalid_isbn(): void
    {
        $response = $this->post('/XX_module_d/isbn-validate', [
            'isbns' => '1234567890123',
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('allValid', false);
    }

    public function test_isbn_validation_submit_multiple_lines(): void
    {
        $book1 = $this->createBook($this->activePublisher, '978986181001', 'Book A');
        $book2 = $this->createBook($this->activePublisher, '978986181018', 'Book B');

        $response = $this->post('/XX_module_d/isbn-validate', [
            'isbns' => $book1->isbn13_digits . "\n" . $book2->isbn13_digits,
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('allValid', true);
        $results = $response->viewData('results');
        $this->assertCount(2, $results);
    }

    public function test_isbn_validation_submit_mixed_valid_invalid(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Book A');

        $response = $this->post('/XX_module_d/isbn-validate', [
            'isbns' => $book->isbn13_digits . "\n" . '0000000000000',
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('allValid', false);
    }

    public function test_isbn_validation_hidden_book_not_valid(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Hidden', hidden: true);

        $response = $this->post('/XX_module_d/isbn-validate', [
            'isbns' => $book->isbn13_digits,
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('allValid', false);
    }

    public function test_isbn_validation_requires_isbns_field(): void
    {
        $response = $this->post('/XX_module_d/isbn-validate', []);
        $response->assertStatus(302); // validation redirect
    }

    // ── Public Book Show (/XX_module_d/01/{isbn}) ──────────────────

    public function test_public_book_show_returns_200(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Public Book');

        $response = $this->get('/XX_module_d/01/' . $book->isbn13_hyphenated);
        $response->assertStatus(200);
    }

    public function test_public_book_show_by_digits(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Digit Book');

        $response = $this->get('/XX_module_d/01/' . $book->isbn13_digits);
        $response->assertStatus(200);
    }

    public function test_public_book_show_hidden_returns_404(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Hidden Book', hidden: true);

        $response = $this->get('/XX_module_d/01/' . $book->isbn13_digits);
        $response->assertStatus(404);
    }

    public function test_public_book_show_inactive_publisher_returns_404(): void
    {
        $book = $this->createBook($this->inactivePublisher, '978986999001', 'Inactive PB Book');

        $response = $this->get('/XX_module_d/01/' . $book->isbn13_digits);
        $response->assertStatus(404);
    }

    public function test_public_book_show_nonexistent_returns_404(): void
    {
        $response = $this->get('/XX_module_d/01/9789861817286');
        $response->assertStatus(404);
    }

    public function test_public_book_show_invalid_isbn_format_returns_404(): void
    {
        // 2 hyphens = invalid format
        $response = $this->get('/XX_module_d/01/978-986-1817286');
        $response->assertStatus(404);
    }

    public function test_public_book_show_3_hyphens_returns_404(): void
    {
        $response = $this->get('/XX_module_d/01/978-9-8-61817286');
        $response->assertStatus(404);
    }

    public function test_public_book_show_valid_1_hyphen_format(): void
    {
        $book = $this->createBook($this->activePublisher, '978986181001', 'Hyphen Book');
        // 1 hyphen: 12digits-checkdigit
        $isbn = substr($book->isbn13_digits, 0, 12) . '-' . substr($book->isbn13_digits, 12, 1);

        $response = $this->get('/XX_module_d/01/' . $isbn);
        $response->assertStatus(200);
    }

    // ── Public Publisher Show ──────────────────────────────────────

    public function test_public_publisher_show_returns_200(): void
    {
        $response = $this->get('/XX_module_d/publishers/' . $this->activePublisher->id);
        $response->assertStatus(200);
    }

    public function test_public_publisher_show_inactive_returns_404(): void
    {
        $response = $this->get('/XX_module_d/publishers/' . $this->inactivePublisher->id);
        $response->assertStatus(404);
    }

    public function test_public_publisher_shows_books(): void
    {
        $this->createBook($this->activePublisher, '978986181001', 'Pub Book A');
        $this->createBook($this->activePublisher, '978986181018', 'Pub Book B');

        $response = $this->get('/XX_module_d/publishers/' . $this->activePublisher->id);
        $response->assertStatus(200);
        $response->assertSee('Pub Book A');
        $response->assertSee('Pub Book B');
    }

    public function test_public_publisher_excludes_hidden_books(): void
    {
        $this->createBook($this->activePublisher, '978986181001', 'Visible');
        $this->createBook($this->activePublisher, '978986181018', 'Hidden', hidden: true);

        $response = $this->get('/XX_module_d/publishers/' . $this->activePublisher->id);
        $response->assertStatus(200);
        $response->assertSee('Visible');
        $response->assertDontSee('Hidden');
    }

    public function test_public_publisher_nonexistent_returns_404(): void
    {
        $response = $this->get('/XX_module_d/publishers/99999');
        $response->assertStatus(404);
    }
}
