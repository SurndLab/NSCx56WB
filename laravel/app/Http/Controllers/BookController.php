<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use App\Services\IsbnService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private readonly IsbnService $isbnService)
    {
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $query = Book::with(['publisher', 'images']);

        if ($search = $request->string('query')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                    ->orWhere('book_description', 'like', "%{$search}%")
                    ->orWhere('book_author', 'like', "%{$search}%")
                    ->orWhere('isbn13_hyphenated', 'like', "%{$search}%")
                    ->orWhere('isbn13_digits', 'like', "%{$search}%");
            });
        }

        if ($user->isPublisherAdmin()) {
            $query->where('publisher_id', $user->publisher_id);
        }

        return $query->orderByDesc('id')->paginate(20);
    }

    public function create()
    {
        return response()->json(['message' => 'Render create book form']);
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'publisher_id' => ['required', 'exists:publishers,id'],
            'book_name' => ['required', 'string', 'max:255'],
            'book_description' => ['required', 'string'],
            'book_author' => ['required', 'string', 'max:255'],
            'isbn_12' => ['required', 'string'],
        ]);

        $publisher = Publisher::findOrFail($data['publisher_id']);
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $publisher->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $isbn12 = $this->isbnService->normalize($data['isbn_12']);
        if (strlen($isbn12) !== 12 || (!str_starts_with($isbn12, '978') && !str_starts_with($isbn12, '979'))) {
            return response()->json(['message' => 'ISBN must be 12 digits starting with 978 or 979'], 422);
        }

        $check = $this->isbnService->computeCheckDigitFrom12($isbn12);
        $isbn13 = $isbn12 . $check;

        if (!$this->isbnService->isValid13($isbn13)) {
            return response()->json(['message' => 'Invalid ISBN checksum'], 422);
        }

        $publisherCode = $publisher->publisher_isbn_code;
        $offset = 6;
        $inputPublisherCode = substr($isbn13, $offset, strlen($publisherCode));
        if ($inputPublisherCode !== $publisherCode) {
            return response()->json(['message' => 'ISBN publisher code does not match selected publisher'], 422);
        }

        $hyphenated = $this->isbnService->hyphenate($isbn13, $publisherCode);

        $book = Book::create([
            'publisher_id' => $publisher->id,
            'book_name' => $data['book_name'],
            'book_description' => $data['book_description'],
            'book_author' => $data['book_author'],
            'isbn13_digits' => $isbn13,
            'isbn13_hyphenated' => $hyphenated,
            'isbn_prefix' => substr($isbn13, 0, 3),
            'registration_group' => substr($isbn13, 3, 3),
            'publisher_code' => $publisherCode,
            'publication_code' => substr($isbn13, $offset + strlen($publisherCode), 12 - ($offset + strlen($publisherCode))),
            'check_digit' => (string) $check,
            'is_hidden' => false,
        ]);

        return response()->json($book, 201);
    }

    public function show(Request $request, string $isbn)
    {
        /** @var User $user */
        $user = $request->user();
        $digits = $this->isbnService->normalize($isbn);

        $query = Book::with(['publisher', 'images'])
            ->where(function ($q) use ($digits, $isbn) {
                $q->where('isbn13_digits', $digits)
                    ->orWhere('isbn13_hyphenated', $isbn);
            });

        if ($user->isPublisherAdmin()) {
            $query->where('publisher_id', $user->publisher_id);
        }

        $book = $query->firstOrFail();

        return response()->json($book);
    }

    public function update(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'book_name' => ['required', 'string', 'max:255'],
            'book_description' => ['required', 'string'],
            'book_author' => ['required', 'string', 'max:255'],
        ]);

        $book->update($data);

        return response()->json($book->fresh(['publisher', 'images']));
    }

    public function hide(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $book->update(['is_hidden' => true]);

        return response()->json(['message' => 'Book hidden']);
    }

    public function showBook(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $book->update(['is_hidden' => false]);

        return response()->json(['message' => 'Book visible']);
    }

    public function destroy(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$book->is_hidden) {
            return response()->json(['message' => 'Only hidden books can be deleted permanently'], 422);
        }

        $book->forceDelete();

        return response()->json(['message' => 'Book permanently deleted']);
    }
}
