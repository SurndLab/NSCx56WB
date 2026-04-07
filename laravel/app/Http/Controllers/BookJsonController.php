<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\IsbnService;
use Illuminate\Http\Request;

class BookJsonController extends Controller
{
    public function __construct(private readonly IsbnService $isbnService)
    {
    }

    public function index(Request $request)
    {
        $query = Book::query()->publicVisible()->with(['publisher', 'images']);

        if ($keyword = $request->string('query')->toString()) {
            $query->where(function ($q) use ($keyword) {
                $q->where('book_name', 'like', "%{$keyword}%")
                    ->orWhere('book_description', 'like', "%{$keyword}%");
            });
        }

        $books = $query->orderBy('id')->paginate(3);

        return response()->json([
            'data' => $books->map(function (Book $book) {
                $cover = $book->images->first();

                return [
                    'book_name' => $book->book_name,
                    'book_isbn' => $book->isbn13_hyphenated,
                    'book_author' => $book->book_author,
                    'publisher_name' => $book->publisher->publisher_name,
                    'cover_image' => $cover ? asset('storage/' . $cover->image_path) : asset('images/default-book.jpg'),
                ];
            })->values(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'total_pages' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'next_page_url' => $books->nextPageUrl(),
                'prev_page_url' => $books->previousPageUrl(),
            ],
        ]);
    }

    public function show(string $isbn)
    {
        $digits = $this->isbnService->normalize($isbn);

        if (!$this->isbnService->isValid13($digits)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $book = Book::query()
            ->publicVisible()
            ->with(['publisher.contacts', 'images'])
            ->where('isbn13_digits', $digits)
            ->first();

        if (!$book) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'book_name' => $book->book_name,
            'book_description' => $book->book_description,
            'book_isbn' => $book->isbn13_hyphenated,
            'book_author' => $book->book_author,
            'images' => $book->images->map(fn ($image) => asset('storage/' . $image->image_path))->values(),
            'publisher' => [
                'publisher_name' => $book->publisher->publisher_name,
                'publisher_address' => $book->publisher->publisher_address,
                'publisher_phone' => $book->publisher->publisher_phone,
                'publisher_isbn' => $book->publisher->publisher_isbn_code,
                'contacts' => $book->publisher->contacts->map(fn ($contact) => [
                    'contact_name' => $contact->contact_name,
                    'contact_phone' => $contact->contact_phone,
                    'contact_email' => $contact->contact_email,
                ])->values(),
            ],
        ]);
    }
}
