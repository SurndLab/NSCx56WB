@extends('layouts.app')
@section('title', isset($admin) ? '編輯出版社管理員' : '新增出版社管理員')
@section('content')
<h1 style="font-size:1.5rem; margin-bottom:1rem;">{{ isset($admin) ? '編輯管理員' : '新增管理員' }} — {{ $publisher->publisher_name }}</h1>
<div class="card">
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ isset($admin) ? route('publisher-admins.update', [$publisher->id, $admin->id]) : route('publisher-admins.store', $publisher->id) }}">
        @csrf
        @if(isset($admin)) @method('PUT') @endif

        <div class="form-group">
            <label for="username">帳號</label>
            <input type="text" id="username" name="username" value="{{ old('username', $admin->username ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="password">密碼{{ isset($admin) ? '（留空則不更改）' : '' }}</label>
            <input type="password" id="password" name="password" {{ isset($admin) ? '' : 'required' }}>
        </div>
        <div class="form-group">
            <label for="display_name">姓名</label>
            <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $admin->display_name ?? '') }}" required>
        </div>
        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($admin) ? '儲存變更' : '建立管理員' }}</button>
            <a href="{{ route('publishers.show', $publisher->id) }}" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>
@endsection
