@extends('layouts.app')
@section('title', $publisher->publisher_name)
@section('content')
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">{{ $publisher->publisher_name }}</h1>
    <div class="flex gap-1">
        <a href="{{ route('publishers.edit', $publisher->id) }}" class="btn btn-warning">編輯</a>
        <a href="{{ route('publishers.index') }}" class="btn btn-secondary">返回列表</a>
    </div>
</div>

<div class="card">
    <h2>出版社資訊</h2>
    <table>
        <tr><th style="width:140px;">ISBN 代碼</th><td style="font-family:monospace;">{{ $publisher->publisher_isbn_code }}</td></tr>
        <tr><th>地址</th><td>{{ $publisher->publisher_address }}</td></tr>
        <tr><th>電話</th><td>{{ $publisher->publisher_phone }}</td></tr>
        <tr>
            <th>狀態</th>
            <td>
                @if($publisher->is_active)
                    <span class="badge badge-green">啟用中</span>
                @else
                    <span class="badge badge-red">已停用</span>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <h2>聯絡人</h2>
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

<div class="card">
    <div class="flex justify-between items-center flex-wrap gap-1 mb-1">
        <h2>出版社管理員</h2>
        <a href="{{ route('publisher-admins.create', $publisher->id) }}" class="btn btn-primary btn-sm">+ 新增管理員</a>
    </div>
    @if($publisher->admins->isEmpty())
        <p class="text-muted">尚未設定管理員。</p>
    @else
        <table>
            <thead><tr><th>帳號</th><th>姓名</th><th>狀態</th><th>操作</th></tr></thead>
            <tbody>
                @foreach($publisher->admins as $admin)
                <tr>
                    <td>{{ $admin->username }}</td>
                    <td>{{ $admin->display_name }}</td>
                    <td>
                        @if($admin->is_active)
                            <span class="badge badge-green">啟用</span>
                        @else
                            <span class="badge badge-red">停用</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <a href="{{ route('publisher-admins.edit', [$publisher->id, $admin->id]) }}" class="btn btn-warning btn-sm">編輯</a>
                            <form method="POST" action="{{ route('publisher-admins.destroy', [$publisher->id, $admin->id]) }}" onsubmit="return confirm('確定刪除此管理員？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">刪除</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card">
    <h2>所屬書籍</h2>
    @php $pubBooks = $publisher->books()->with('images')->get(); @endphp
    @if($pubBooks->isEmpty())
        <p class="text-muted">尚無書籍。</p>
    @else
        <table>
            <thead><tr><th>書名</th><th class="hide-mobile">ISBN</th><th class="hide-mobile">作者</th><th>狀態</th></tr></thead>
            <tbody>
                @foreach($pubBooks as $book)
                <tr>
                    <td><a href="{{ route('books.show', $book->isbn13_hyphenated) }}">{{ $book->book_name }}</a></td>
                    <td class="hide-mobile" style="font-family:monospace;">{{ $book->isbn13_hyphenated }}</td>
                    <td class="hide-mobile">{{ $book->book_author }}</td>
                    <td>
                        @if($book->is_hidden)
                            <span class="badge badge-red">隱藏</span>
                        @else
                            <span class="badge badge-green">顯示</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
