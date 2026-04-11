@extends('layouts.public')
@section('title', $publisher->publisher_name)
@section('content')
<h1>{{ $publisher->publisher_name }}</h1>

<div class="card">
    <h2 style="margin-bottom:.75rem; font-size:1.1rem;">出版社資訊</h2>
    <table>
        <tr><th style="width:120px;">地址</th><td>{{ $publisher->publisher_address }}</td></tr>
        <tr><th>電話</th><td>{{ $publisher->publisher_phone }}</td></tr>
        <tr><th>ISBN 代碼</th><td style="font-family:monospace;">{{ $publisher->publisher_isbn_code }}</td></tr>
    </table>
</div>

<div class="card">
    <h2 style="margin-bottom:.75rem; font-size:1.1rem;">聯絡人</h2>
    <table>
        <thead><tr><th>姓名</th><th>電話</th><th>Email</th></tr></thead>
        <tbody>
            @foreach($publisher->contacts as $c)
            <tr>
                <td>{{ $c->contact_name }}</td>
                <td>{{ $c->contact_phone }}</td>
                <td>{{ $c->contact_email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<h2 style="margin:1.5rem 0 .75rem; font-size:1.25rem;">書籍列表</h2>
@if($books->isEmpty())
    <p class="text-muted">此出版社目前沒有可顯示的書籍。</p>
@else
    @foreach($books as $book)
    <div class="book-card">
        @if($book->images->first())
            <img src="{{ asset('storage/' . $book->images->first()->image_path) }}" alt="{{ $book->book_name }}">
        @else
            <div class="no-img">無圖片</div>
        @endif
        <div class="info">
            <h3><a href="{{ route('public.book.show', $book->isbn13_digits) }}">{{ $book->book_name }}</a></h3>
            <p><strong>作者：</strong>{{ $book->book_author }}</p>
            <p><strong>ISBN：</strong><span style="font-family:monospace;">{{ $book->isbn13_hyphenated }}</span></p>
            <p>{{ Str::limit($book->book_description, 120) }}</p>
        </div>
    </div>
    @endforeach
@endif
@endsection
