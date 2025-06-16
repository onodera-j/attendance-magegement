@extends("layouts.auth")

@section("css")
<link rel="stylesheet" href="{{ asset('css/account.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="content-title">
        <h2>一般ログイン</h2>
    </div>

    <div class="account-form">
        <form method="POST" action="{{route("login")}}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">メールアドレス</label>
                <input class="form-text" id="email" name="email" type="email">
                @error('email')
                {{ $message }}
                @enderror

            </div>
            <div class="form-group">
                <label class="form-label" for="password">パスワード</label>
                <input class="form-text" id="password" name="password" type="password">
                @error('password')
                {{ $message }}
                @enderror
            </div>


            <div class="form-btn">
                <button class="btn-submit">ログイン</button>
            </div>
        </form>

        <div class="link">
            <a class="link-login" href="/register">会員登録はこちら</a>
        </div>

    </div>
</div>


@endsection
