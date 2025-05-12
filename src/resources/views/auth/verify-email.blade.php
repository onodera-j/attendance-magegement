@extends('layouts.auth')

@section("css")
<link rel="stylesheet" href="{{ asset('css/verify.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="card-body">
        @if (session('resent'))
            <div class="alert alert-success" role="alert">
                {{ __('登録していただいたメールアドレスに認証メールを送付しました。') }}
            </div>
        @endif
        {{ __('メール認証を完了してください。') }}
        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn">{{ __('認証はこちらから') }}</button>
        </form>
    </div>
</div>
@endsection
