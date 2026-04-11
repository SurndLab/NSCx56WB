@extends('layouts.public')
@section('title', 'ISBN 批量驗證')
@section('content')
<h1>ISBN 批量驗證</h1>

@if(isset($allValid) && $allValid)
    <div class="all-valid-banner">
        <div class="check">✅</div>
        All valid
    </div>
@endif

@if(isset($results))
    <div class="card" style="margin-bottom:1rem;">
        <h2 style="margin-bottom:.75rem; font-size:1.1rem;">驗證結果</h2>
        @foreach($results as $r)
            <div class="isbn-result {{ $r['valid'] ? 'isbn-valid' : 'isbn-invalid' }}">
                <strong>{{ $r['input'] }}</strong>
                → {{ $r['valid'] ? '✅ 有效' : '❌ 無效' }}
            </div>
        @endforeach
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('public.isbn.validate.submit') }}">
        @csrf
        <div class="form-group">
            <label for="isbns">請輸入 ISBN（每行一個，連字號可省略）</label>
            <textarea id="isbns" name="isbns" rows="8" placeholder="978-986-181-728-6&#10;9789861817286&#10;978-957-123-456-7">{{ old('isbns', $inputIsbns ?? '') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">驗證</button>
    </form>
</div>
@endsection
