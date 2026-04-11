<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Publisher;
use App\Services\IsbnService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct(private readonly IsbnService $isbnService)
    {
    }

    public function isbnValidationPage()
    {
        return view('public.isbn-validate');
    }

    public function isbnValidationSubmit(Request $request)
    {
        $payload = $request->validate([
            'isbns' => ['required', 'string'],
        ]);

        $lines = preg_split('/\R+/', trim($payload['isbns'])) ?: [];
        $results = [];
        $allValid = true;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $digits = $this->isbnService->normalize($line);
            $valid = false;

            if ($this->isbnService->isValid13($digits)) {
                $valid = Book::query()
                    ->publicVisible()
                    ->where('isbn13_digits', $digits)
                    ->exists();
            }

            $results[] = [
                'input' => $line,
                'normalized' => $digits,
                'valid' => $valid,
            ];

            if (!$valid) {
                $allValid = false;
            }
        }

        return view('public.isbn-validate', [
            'results' => $results,
            'allValid' => $allValid,
            'inputIsbns' => $payload['isbns'],
        ]);
    }

    public function bookShow(string $isbn)
    {
        // Validate ISBN format before normalizing:
        // - No hyphens: exactly 13 digits
        // - 1 hyphen: 12 digits + hyphen + 1 check digit
        // - 4 hyphens: 3-digit prefix, variable groups, 1-digit check (standard ISBN-13)
        // Any other hyphenation pattern is 404
        if (!$this->isValidIsbnFormat($isbn)) {
            abort(404);
        }

        $digits = $this->isbnService->normalize($isbn);
        if (!$this->isbnService->isValid13($digits)) {
            abort(404);
        }

        $book = Book::query()
            ->publicVisible()
            ->with(['publisher.contacts', 'images'])
            ->where('isbn13_digits', $digits)
            ->first();

        if (!$book) {
            abort(404);
        }

        return view('public.book', compact('book'));
    }

    private function isValidIsbnFormat(string $isbn): bool
    {
        $hyphenCount = substr_count($isbn, '-');

        if ($hyphenCount === 0) {
            return (bool) preg_match('/^\d{13}$/', $isbn);
        }

        if ($hyphenCount === 1) {
            return (bool) preg_match('/^\d{12}-\d$/', $isbn);
        }

        if ($hyphenCount === 4) {
            // Standard 5-group ISBN-13: prefix(3) - group - publisher - publication - check(1)
            return (bool) preg_match('/^\d{3}-\d+-\d+-\d+-\d$/', $isbn);
        }

        return false;
    }

    public function publisherShow(Publisher $publisher)
    {
        if (!$publisher->is_active) {
            abort(404);
        }

        $publisher->load('contacts');
        $books = $publisher->books()->publicVisible()->with('images')->get();

        return view('public.publisher', compact('publisher', 'books'));
    }
}
