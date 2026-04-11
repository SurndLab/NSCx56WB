@extends('layouts.app')
@section('title', '已停用出版社')
@section('content')
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">已停用出版社</h1>
    <a href="{{ route('publishers.index') }}" class="btn btn-secondary">← 返回出版社列表</a>
</div>
<div class="card">
    @if($publishers->isEmpty())
        <p class="text-muted">目前沒有已停用的出版社。</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>名稱</th>
                    <th class="hide-mobile">ISBN 代碼</th>
                    <th class="hide-mobile">電話</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($publishers as $pub)
                <tr>
                    <td>{{ $pub->publisher_name }}</td>
                    <td class="hide-mobile" style="font-family:monospace;">{{ $pub->publisher_isbn_code }}</td>
                    <td class="hide-mobile">{{ $pub->publisher_phone }}</td>
                    <td>
                        <form method="POST" action="{{ route('publishers.enable', $pub->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-success btn-sm">重新啟用</button>
                        </form>
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
