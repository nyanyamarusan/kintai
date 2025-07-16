@extends('layouts.app')

@section('content')
<div class="container inter text fw-bold">
    <h2 class="text-center form-title pt-8p fw-bold">会員登録</h2>
    <form action="/register" class="w-50 mx-auto" method="post">
        @csrf
        <div class="mt-5p">
            <label for="name" class="form-label">名前</label>
            <input type="text" class="form-control rounded-1 border-black h-2vw98"
                id="name" name="name">
            @error('name')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5p">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" class="form-control rounded-1 border-black h-2vw98"
                id="email" name="email">
            @error('email')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5p">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" class="form-control rounded-1 border-black h-2vw98"
                id="password" name="password">
            @error('password')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mt-5p">
            <label for="password_confirmation" class="form-label">パスワード確認</label>
            <input type="password" class="form-control rounded-1 border-black h-2vw98"
                id="password_confirmation" name="password_confirmation">
            @error('password')
                @if (str_contains($message, '一致'))
                    <p class="text-danger">{{ $message }}</p>
                @endif
            @enderror
        </div>
        <button type="submit" class="btn text-1vw72 text-white fw-bold bg-black rounded-2 w-100 mt-10p h-4vw">
            登録する
        </button>
    </form>
    <div class="text-center mt-2p">
        <a href="/login" class="link text-decoration-none fw-normal">ログインはこちら</a>
    </div>
</div>
@endsection
