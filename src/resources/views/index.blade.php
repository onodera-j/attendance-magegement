@extends('layouts.auth')

@section("css")
<link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="content-title">
        <h2>勤怠管理</h2>
    </div>
    <div class="guide">
        <div class="user login">
            <a class="link" href="/login">従業員ログイン</a>
        </div>
        <div class="admin login">
            <a class="link" href="/admin/login/">管理者ログイン</a>
        </div>

    </div>
</div>

@endsection
