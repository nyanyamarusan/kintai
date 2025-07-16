@extends('layouts.app')

@section('content')
<div class="container inter text fw-bold">
    <h2 class="text-center form-title pt-8p fw-bold">ログイン</h2>
    <form action="/admin/login" class="w-50 mx-auto" method="post">
        @csrf
        <div class="mt-10p">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" class="form-control rounded-1 border-black h-4vw"
                id="email" name="email">
            @error('email')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-10p">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" class="form-control rounded-1 border-black h-4vw"
                id="password" name="password">
            @error('password')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn text-1vw72 text-white fw-bold bg-black rounded-2 w-100 mt-10p h-4vw">
            ログインする
        </button>
    </form>
    <div class="text-center mt-2p">
        <a href="/register" class="link text-decoration-none fw-normal">会員登録はこちら</a>
    </div>
</div>
@endsection
