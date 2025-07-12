@extends('layouts.app')

@section('content')
<div class="container inter text">
    <h2 class="text-center title pt-8p fw-bold">会員登録</h2>
    <form action="/register" class="w-50 mx-auto" method="post">
        @csrf
        <div class="mt-5p">
            <label for="name" class="fw-bold mb-1p">名前</label>
            <input type="text" class="form-control input" id="name" name="name">
            @error('name')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5p">
            <label for="email" class="fw-bold mb-1p">メールアドレス</label>
            <input type="email" class="form-control input" id="email" name="email">
            @error('email')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5p">
            <label for="password" class="fw-bold mb-1p">パスワード</label>
            <input type="password" class="form-control input" id="password" name="password">
            @error('password')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5p">
            <label for="password_confirmation" class="fw-bold mb-1p">パスワード確認</label>
            <input type="password" class="form-control input" id="password_confirmation" name="password_confirmation">
            @error('password')
                @if (str_contains($message, '一致'))
                    <p class="text-danger">{{ $message }}</p>
                @endif
            @enderror
        </div>
        <button type="submit" class="bg-black btn-text w-100 btn-border p-1_5p mt-10p">登録する</button>
    </form>
    <div class="text-center mt-2p">
        <a href="/login" class="link">ログインはこちら</a>
    </div>
</div>
@endsection
