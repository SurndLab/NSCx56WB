@extends('layouts.app')
@section('title', $book->book_name)
@section('content')
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">{{ $book->book_name }}</h1>
    <div class="flex gap-1">
        <a href="{{ route('books.edit', $book->isbn13_hyphenated) }}" class="btn btn-warning">編輯</a>
        <a href="{{ route('books.index') }}" class="btn btn-secondary">返回列表</a>
    </div>
</div>
<div class="card">
    <table>
        <tr><th style="width:120px;">ISBN</th><td style="font-family:monospace;">{{ $book->isbn13_hyphenated }}</td></tr>
        <tr><th>作者</th><td>{{ $book->book_author }}</td></tr>
        <tr><th>出版社</th><td>{{ $book->publisher->publisher_name ?? '-' }}</td></tr>
        <tr>
            <th>狀態</th>
            <td>
                @if($book->is_hidden)
                    <span class="badge badge-red">已隱藏</span>
                @else
                    <span class="badge badge-green">顯示中</span>
                @endif
            </td>
        </tr>
        <tr><th>描述</th><td>{!! nl2br(e($book->book_description)) !!}</td></tr>
    </table>
</div>

<div class="card">
    <h2>書籍圖片</h2>
    @if($book->images->isEmpty())
        <p class="text-muted">尚未上傳圖片（使用預設圖片）。</p>
    @else
        <div class="image-grid">
            @foreach($book->images as $i => $img)
                <div class="img-wrapper">
                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="圖片 {{ $i + 1 }}">
                    @if($i === 0)
                        <span class="badge badge-green" style="position:absolute; bottom:4px; left:4px;">封面</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="card">
    <h2>操作</h2>
    <div class="flex gap-1 flex-wrap">
        @if($book->is_hidden)
            <form method="POST" action="{{ route('books.show-book', $book->id) }}">
                @csrf @method('PATCH')
                <button class="btn btn-success">重新顯示</button>
            </form>
            <form method="POST" action="{{ route('books.destroy', $book->id) }}" onsubmit="return confirm('確定要永久刪除此書籍？此操作無法復原！')">
                @csrf @method('DELETE')
                <button class="btn btn-danger">永久刪除</button>
            </form>
        @else
            <form method="POST" action="{{ route('books.hide', $book->id) }}">
                @csrf @method('PATCH')
                <button class="btn btn-secondary">隱藏書籍</button>
            </form>
        @endif
    </div>
</div>
@endsection
