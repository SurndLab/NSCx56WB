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
        return response()->json(['message' => 'Render public ISBN batch validation page']);
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
            $digits = $this->isbnService->normalize($line);
            $valid = Book::query()
                ->publicVisible()
                ->where('isbn13_digits', $digits)
                ->exists();

            $results[] = [
                'input' => $line,
                'normalized' => $digits,
                'valid' => $valid,
            ];

            if (!$valid) {
                $allValid = false;
            }
        }

        return response()->json([
            'all_valid' => $allValid,
            'message' => $allValid ? 'All valid' : 'Contains invalid ISBN(s)',
            'results' => $results,
        ]);
    }

    public function bookShow(string $isbn)
    {
        $digits = $this->isbnService->normalize($isbn);
        if (!$this->isbnService->isValid13($digits)) {
            abort(404);
        }

        $book = Book::query()
            ->publicVisible()
            ->with(['publisher', 'images'])
            ->where('isbn13_digits', $digits)
            ->firstOrFail();

        return response()->json($book);
    }

    public function publisherShow(Publisher $publisher)
    {
        if (!$publisher->is_active) {
            abort(404);
        }

        return response()->json([
            'publisher' => $publisher->load('contacts'),
            'books' => $publisher->books()->publicVisible()->with('images')->get(),
        ]);
    }
}
