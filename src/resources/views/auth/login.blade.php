@extends('layouts.app')

@section('content')
<div class="container inter text">
    <h2 class="text-center title pt-8p fw-bold">ログイン</h2>
    <form action="/admin/login" class="w-50 mx-auto" method="post">
        @csrf
        <div class="mt-10p">
            <label for="email" class="fw-bold mb-1p">メールアドレス</label>
            <input type="email" class="form-control input h-60" id="email" name="email">
            @error('email')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-10p">
            <label for="password" class="fw-bold mb-1p">パスワード</label>
            <input type="password" class="form-control input h-60" id="password" name="password">
            @error('password')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="bg-black btn-text w-100 btn-border p-1_5p mt-15p">ログインする</button>
    </form>
    <div class="text-center mt-2p">
        <a href="/register" class="link">会員登録はこちら</a>
    </div>
</div>
@endsection
