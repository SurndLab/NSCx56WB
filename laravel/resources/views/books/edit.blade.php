@extends('layouts.app')
@section('title', '編輯書籍 - ' . $book->book_name)
@section('content')
<h1 style="font-size:1.5rem; margin-bottom:1rem;">編輯書籍</h1>
<div class="card">
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('books.update', $book->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" value="{{ $book->isbn13_hyphenated }}" disabled style="background:#f3f4f6;">
        </div>
        <div class="form-group">
            <label>出版社</label>
            <input type="text" value="{{ $book->publisher->publisher_name ?? '-' }}" disabled style="background:#f3f4f6;">
        </div>
        <div class="form-group">
            <label for="book_name">書籍名稱</label>
            <input type="text" id="book_name" name="book_name" value="{{ old('book_name', $book->book_name) }}" required>
        </div>
        <div class="form-group">
            <label for="book_description">描述</label>
            <textarea id="book_description" name="book_description" rows="4" required>{{ old('book_description', $book->book_description) }}</textarea>
        </div>
        <div class="form-group">
            <label for="book_author">作者</label>
            <input type="text" id="book_author" name="book_author" value="{{ old('book_author', $book->book_author) }}" required>
        </div>

        <h3 class="mb-1">現有圖片</h3>
        @if($book->images->isEmpty())
            <p class="text-muted mb-2">尚未上傳圖片。</p>
        @else
            <div class="image-grid mb-2">
                @foreach($book->images as $i => $img)
                    <div class="img-wrapper">
                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="圖片 {{ $i + 1 }}">
                        <label style="position:absolute; bottom:4px; left:4px;">
                            <input type="checkbox" name="remove_images[]" value="{{ $img->id }}"> 移除
                        </label>
                        @if($i === 0)
                            <span class="badge badge-green" style="position:absolute; top:4px; left:4px;">封面</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="form-group">
            <label for="images">上傳新圖片（可多選）</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
        </div>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">儲存變更</button>
            <a href="{{ route('books.show', $book->isbn13_hyphenated) }}" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>
@endsection
