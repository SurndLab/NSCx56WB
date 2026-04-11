@extends('layouts.app')
@section('title', '新增書籍')
@section('content')
<h1 style="font-size:1.5rem; margin-bottom:1rem;">新增書籍</h1>
<div class="card">
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="publisher_id">出版社</label>
            <select id="publisher_id" name="publisher_id" required>
                <option value="">-- 選擇出版社 --</option>
                @foreach($publishers as $pub)
                    <option value="{{ $pub->id }}" {{ old('publisher_id') == $pub->id ? 'selected' : '' }}>
                        {{ $pub->publisher_name }} (代碼: {{ $pub->publisher_isbn_code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="book_name">書籍名稱</label>
            <input type="text" id="book_name" name="book_name" value="{{ old('book_name') }}" required>
        </div>
        <div class="form-group">
            <label for="book_description">描述</label>
            <textarea id="book_description" name="book_description" rows="4" required>{{ old('book_description') }}</textarea>
        </div>
        <div class="form-group">
            <label for="book_author">作者</label>
            <input type="text" id="book_author" name="book_author" value="{{ old('book_author') }}" required>
        </div>
        <div class="form-group">
            <label for="isbn_12">ISBN 前 12 位</label>
            <input type="text" id="isbn_12" name="isbn_12" value="{{ old('isbn_12') }}" placeholder="例: 978-986-181-728" required>
            <div class="text-sm text-muted mt-1">系統將自動計算校驗碼。出版社代碼需與所選出版社一致。</div>
        </div>
        <div class="form-group">
            <label for="images">書籍圖片（可多選）</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
            <div class="text-sm text-muted mt-1">第一張圖片將作為封面。</div>
        </div>
        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">建立書籍</button>
            <a href="{{ route('books.index') }}" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>
@endsection
