<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookImage;
use App\Models\Publisher;
use App\Models\User;
use App\Services\IsbnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $books = $query->orderByDesc('id')->paginate(20);

        return view('books.index', compact('books'));
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isPublisherAdmin()) {
            $publishers = Publisher::where('id', $user->publisher_id)->where('is_active', true)->get();
        } else {
            $publishers = Publisher::where('is_active', true)->orderBy('publisher_name')->get();
        }

        return view('books.create', compact('publishers'));
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
            'images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $publisher = Publisher::findOrFail($data['publisher_id']);
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $publisher->id) {
            return back()->withErrors(['publisher_id' => '您只能為所屬出版社新增書籍'])->withInput();
        }

        $isbn12 = $this->isbnService->normalize($data['isbn_12']);
        if (strlen($isbn12) !== 12 || (!str_starts_with($isbn12, '978') && !str_starts_with($isbn12, '979'))) {
            return back()->withErrors(['isbn_12' => 'ISBN 必須為 12 位數字，以 978 或 979 開頭'])->withInput();
        }

        $check = $this->isbnService->computeCheckDigitFrom12($isbn12);
        $isbn13 = $isbn12 . $check;

        if (!$this->isbnService->isValid13($isbn13)) {
            return back()->withErrors(['isbn_12' => 'ISBN 校驗碼錯誤'])->withInput();
        }

        $publisherCode = $publisher->publisher_isbn_code;
        $offset = 6;
        $inputPublisherCode = substr($isbn13, $offset, strlen($publisherCode));
        if ($inputPublisherCode !== $publisherCode) {
            return back()->withErrors(['isbn_12' => 'ISBN 中的出版社代碼與所選出版社不符'])->withInput();
        }

        $hyphenated = $this->isbnService->hyphenate($isbn13, $publisherCode);

        if (Book::where('isbn13_digits', $isbn13)->exists()) {
            return back()->withErrors(['isbn_12' => '此 ISBN 已存在'])->withInput();
        }

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

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $file) {
                $path = $file->store('book-images', 'public');
                BookImage::create([
                    'book_id' => $book->id,
                    'image_path' => $path,
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('books.show', $book->isbn13_hyphenated)->with('success', '書籍已建立');
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

        $book = $query->first();
        if (!$book) {
            if ($user->isPublisherAdmin()) {
                abort(403);
            }
            abort(404);
        }

        return view('books.show', compact('book'));
    }

    public function edit(Request $request, string $isbn)
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

        $book = $query->first();
        if (!$book) {
            if ($user->isPublisherAdmin()) {
                abort(403);
            }
            abort(404);
        }

        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            abort(403);
        }

        $data = $request->validate([
            'book_name' => ['required', 'string', 'max:255'],
            'book_description' => ['required', 'string'],
            'book_author' => ['required', 'string', 'max:255'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', 'exists:book_images,id'],
        ]);

        $book->update([
            'book_name' => $data['book_name'],
            'book_description' => $data['book_description'],
            'book_author' => $data['book_author'],
        ]);

        // Remove selected images
        if (!empty($data['remove_images'])) {
            $imagesToRemove = BookImage::where('book_id', $book->id)
                ->whereIn('id', $data['remove_images'])
                ->get();
            foreach ($imagesToRemove as $img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }
        }

        // Upload new images
        if ($request->hasFile('images')) {
            $maxSort = BookImage::where('book_id', $book->id)->max('sort_order') ?? -1;
            foreach ($request->file('images') as $file) {
                $maxSort++;
                $path = $file->store('book-images', 'public');
                BookImage::create([
                    'book_id' => $book->id,
                    'image_path' => $path,
                    'sort_order' => $maxSort,
                ]);
            }
        }

        return redirect()->route('books.show', $book->isbn13_hyphenated)->with('success', '書籍已更新');
    }

    public function hide(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            abort(403);
        }

        $book->update(['is_hidden' => true]);

        return back()->with('success', '書籍已隱藏');
    }

    public function showBook(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            abort(403);
        }

        $book->update(['is_hidden' => false]);

        return back()->with('success', '書籍已重新顯示');
    }

    public function destroy(Request $request, Book $book)
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isPublisherAdmin() && (int) $user->publisher_id !== (int) $book->publisher_id) {
            abort(403);
        }

        if (!$book->is_hidden) {
            return back()->with('error', '只有已隱藏的書籍可以永久刪除');
        }

        // Delete images from storage
        foreach ($book->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $book->images()->delete();
        $book->forceDelete();

        return redirect()->route('books.index')->with('success', '書籍已永久刪除');
    }
}
