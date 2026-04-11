@extends('layouts.app')
@section('title', '登入 - 書籍管理系統')
@section('content')
<div style="max-width: 400px; margin: 3rem auto;">
    <div class="card">
        <h2 style="text-align: center;">管理員登入</h2>
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="username">帳號</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">登入</button>
        </form>
    </div>
</div>
@endsection
