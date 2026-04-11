@extends('layouts.app')
@section('title', '出版社列表')
@section('content')
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">出版社管理</h1>
    <div class="flex gap-1">
        <a href="{{ route('publishers.inactive') }}" class="btn btn-secondary">已停用出版社</a>
        <a href="{{ route('publishers.create') }}" class="btn btn-primary">+ 新增出版社</a>
    </div>
</div>
<div class="card">
    @if($publishers->isEmpty())
        <p class="text-muted">目前沒有出版社資料。</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>名稱</th>
                    <th class="hide-mobile">ISBN 代碼</th>
                    <th class="hide-mobile">電話</th>
                    <th>聯絡人</th>
                    <th>管理員</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($publishers as $pub)
                <tr>
                    <td><a href="{{ route('publishers.show', $pub->id) }}">{{ $pub->publisher_name }}</a></td>
                    <td class="hide-mobile" style="font-family:monospace;">{{ $pub->publisher_isbn_code }}</td>
                    <td class="hide-mobile">{{ $pub->publisher_phone }}</td>
                    <td>{{ $pub->contacts->count() }}</td>
                    <td>{{ $pub->admins->count() }}</td>
                    <td>
                        <div class="flex gap-1 flex-wrap">
                            <a href="{{ route('publishers.edit', $pub->id) }}" class="btn btn-warning btn-sm">編輯</a>
                            <form method="POST" action="{{ route('publishers.disable', $pub->id) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-danger btn-sm">停用</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $publishers->links('vendor.pagination') }}
        </div>
    @endif
</div>
@endsection
