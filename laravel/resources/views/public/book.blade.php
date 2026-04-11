@extends('layouts.public')
@section('title', $book->book_name)
@section('content')
<h1>{{ $book->book_name }}</h1>

<div class="card">
    @if($book->images->isNotEmpty())
        <div class="image-gallery">
            @foreach($book->images as $img)
                <img src="{{ asset('storage/' . $img->image_path) }}" alt="{{ $book->book_name }}">
            @endforeach
        </div>
    @endif

    <table>
        <tr><th style="width:100px;">作者</th><td>{{ $book->book_author }}</td></tr>
        <tr><th>ISBN</th><td style="font-family:monospace;">{{ $book->isbn13_hyphenated }}</td></tr>
        <tr>
            <th>出版社</th>
            <td><a href="{{ route('public.publisher.show', $book->publisher_id) }}">{{ $book->publisher->publisher_name }}</a></td>
        </tr>
    </table>

    <div style="margin-top:1rem;">
        <h3 style="margin-bottom:.5rem;">書籍描述</h3>
        <p>{!! nl2br(e($book->book_description)) !!}</p>
    </div>
</div>
@endsection
