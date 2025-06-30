@extends('layouts.auth')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/account.css') }}" />
@endsection

@section('content')
    <div class="content">
        <div class="content-title">
            <h2>会員登録</h2>
        </div>

        <div class="account-form">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">ユーザー名</label>
                    <input class="form-text" id="name" name="name" type="text">
                    @error('name')
                        {{ $message }}
                    @enderror
                </div>
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
                <div class="form-group">
                    <label class="form-label" for="confirm">確認用パスワード</label>
                    <input class="form-text" id="confirm" name="password_confirmation" type="password">
                    @error('password_confirmation')
                        {{ $message }}
                    @enderror

                </div>

                <div class="form-btn">
                    <button class="btn-submit">登録する</button>
                </div>
            </form>

            <div class="link">
                <a class="link-login" href="/login">ログインはこちら</a>
            </div>

        </div>
    @endsection
