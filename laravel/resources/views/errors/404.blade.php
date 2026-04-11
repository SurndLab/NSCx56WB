@extends('layouts.public')
@section('title', '404 - 頁面不存在')
@section('content')
<div style="text-align:center; padding:4rem 0;">
    <h1 style="font-size:4rem; color:#dc2626;">404</h1>
    <p style="font-size:1.25rem; color:#6b7280; margin-top:1rem;">找不到您要的頁面</p>
    <a href="{{ url('/XX_module_d/login') }}" class="btn btn-primary" style="margin-top:1.5rem;">回首頁</a>
</div>
@endsection
