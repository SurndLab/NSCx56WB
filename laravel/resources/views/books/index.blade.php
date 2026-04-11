@extends('layouts.app')
@section('title', '書籍列表')
@section('content')
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">書籍列表</h1>
    <a href="{{ route('books.create') }}" class="btn btn-primary">+ 新增書籍</a>
</div>

<div class="card">
    <form method="GET" action="{{ route('books.index') }}" class="flex gap-1 mb-2">
        <input type="text" name="query" value="{{ request('query') }}" placeholder="搜尋書名、作者、ISBN..." style="flex:1; padding:.5rem .75rem; border:1px solid #d1d5db; border-radius:6px;">
        <button type="submit" class="btn btn-primary">搜尋</button>
        @if(request('query'))
            <a href="{{ route('books.index') }}" class="btn btn-secondary">清除</a>
        @endif
    </form>

    @if($books->isEmpty())
        <p class="text-muted">目前沒有書籍資料。</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>封面</th>
                    <th>書名</th>
                    <th class="hide-mobile">作者</th>
                    <th class="hide-mobile">ISBN</th>
                    <th class="hide-mobile">出版社</th>
                    <th>狀態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($books as $book)
                <tr>
                    <td>
                        @if($book->images->first())
                            <img src="{{ asset('storage/' . $book->images->first()->image_path) }}" alt="封面" style="width:50px; height:65px; object-fit:cover; border-radius:4px;">
                        @else
                            <div style="width:50px; height:65px; background:#e5e7eb; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:.7rem; color:#9ca3af;">無圖</div>
                        @endif
                    </td>
                    <td><a href="{{ route('books.show', $book->isbn13_hyphenated) }}">{{ $book->book_name }}</a></td>
                    <td class="hide-mobile">{{ $book->book_author }}</td>
                    <td class="hide-mobile" style="font-family:monospace; font-size:.85rem;">{{ $book->isbn13_hyphenated }}</td>
                    <td class="hide-mobile">{{ $book->publisher->publisher_name ?? '-' }}</td>
                    <td>
                        @if($book->is_hidden)
                            <span class="badge badge-red">已隱藏</span>
                        @else
                            <span class="badge badge-green">顯示中</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-1 flex-wrap">
                            <a href="{{ route('books.edit', $book->isbn13_hyphenated) }}" class="btn btn-warning btn-sm">編輯</a>
                            @if($book->is_hidden)
                                <form method="POST" action="{{ route('books.show-book', $book->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-success btn-sm">顯示</button>
                                </form>
                                <form method="POST" action="{{ route('books.destroy', $book->id) }}" onsubmit="return confirm('確定要永久刪除此書籍？')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">刪除</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('books.hide', $book->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-secondary btn-sm">隱藏</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $books->appends(request()->query())->links('vendor.pagination') }}
        </div>
    @endif
</div>
@endsection
