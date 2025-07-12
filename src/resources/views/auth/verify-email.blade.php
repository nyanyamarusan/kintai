@extends('layouts.app')

@section('content')
<div class="container inter text text-center">
    <p class="mt-15p fw-bold">登録していただいたメールアドレスに認証メールを送付しました。<br>
    メール認証を完了してください。</p>
    <a href="http://localhost:8025/" target="_blank" class="verify-email-btn mt-5p fw-bold">認証はこちらから</a>
    <form action="/email/verification-notification" method="post">
        @csrf
        <button type="submit" class="link mt-5p fw-normal border-0">認証メールを再送する</button>
    </form>
</div>
@endsection
