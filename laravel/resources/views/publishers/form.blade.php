@extends('layouts.app')
@section('title', isset($publisher) ? '編輯出版社' : '新增出版社')
@section('content')
<h1 style="font-size:1.5rem; margin-bottom:1rem;">{{ isset($publisher) ? '編輯出版社' : '新增出版社' }}</h1>
<div class="card">
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ isset($publisher) ? route('publishers.update', $publisher->id) : route('publishers.store') }}">
        @csrf
        @if(isset($publisher)) @method('PUT') @endif

        <div class="form-group">
            <label for="publisher_name">出版社名稱</label>
            <input type="text" id="publisher_name" name="publisher_name" value="{{ old('publisher_name', $publisher->publisher_name ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="publisher_address">地址</label>
            <input type="text" id="publisher_address" name="publisher_address" value="{{ old('publisher_address', $publisher->publisher_address ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="publisher_phone">電話號碼</label>
            <input type="text" id="publisher_phone" name="publisher_phone" value="{{ old('publisher_phone', $publisher->publisher_phone ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="publisher_isbn_code">ISBN 出版社代碼</label>
            <input type="text" id="publisher_isbn_code" name="publisher_isbn_code" value="{{ old('publisher_isbn_code', $publisher->publisher_isbn_code ?? '') }}" required>
        </div>

        <h3 class="mb-1">聯絡人（至少一位）</h3>
        <div id="contacts-container">
            @php
                $contacts = old('contacts', isset($publisher) ? $publisher->contacts->map(fn($c) => [
                    'contact_name' => $c->contact_name,
                    'contact_phone' => $c->contact_phone,
                    'contact_email' => $c->contact_email,
                ])->toArray() : [['contact_name' => '', 'contact_phone' => '', 'contact_email' => '']]);
            @endphp
            @foreach($contacts as $idx => $contact)
            <div class="contact-row card" style="padding:.75rem; margin-bottom:.5rem; background:#f9fafb;">
                <div class="flex justify-between items-center mb-1">
                    <strong class="text-sm">聯絡人 #{{ $idx + 1 }}</strong>
                    @if($idx > 0)
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.contact-row').remove()">移除</button>
                    @endif
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.5rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>姓名</label>
                        <input type="text" name="contacts[{{ $idx }}][contact_name]" value="{{ $contact['contact_name'] }}" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>電話</label>
                        <input type="text" name="contacts[{{ $idx }}][contact_phone]" value="{{ $contact['contact_phone'] }}" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Email</label>
                        <input type="email" name="contacts[{{ $idx }}][contact_email]" value="{{ $contact['contact_email'] }}" required>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="addContact()">+ 新增聯絡人</button>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($publisher) ? '儲存變更' : '建立出版社' }}</button>
            <a href="{{ route('publishers.index') }}" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>

<script>
let contactIdx = {{ count($contacts) }};
function addContact() {
    const container = document.getElementById('contacts-container');
    const html = `<div class="contact-row card" style="padding:.75rem; margin-bottom:.5rem; background:#f9fafb;">
        <div class="flex justify-between items-center mb-1">
            <strong class="text-sm">聯絡人 #${contactIdx + 1}</strong>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.contact-row').remove()">移除</button>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.5rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label>姓名</label>
                <input type="text" name="contacts[${contactIdx}][contact_name]" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>電話</label>
                <input type="text" name="contacts[${contactIdx}][contact_phone]" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Email</label>
                <input type="email" name="contacts[${contactIdx}][contact_email]" required>
            </div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    contactIdx++;
}
</script>
@endsection
