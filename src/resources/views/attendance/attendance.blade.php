@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="user-status">
        {{-- @switch($status)
            @case(0)
            <span class="status">勤務外</span>
                @break
            @case(1)
            <span class="status">出勤中</span>
                @break
            @case(2)
            <span class="status">休憩中</span>
                @break
            @case(3)
            <span class="status">退勤済</span>
                @break
        @endswitch --}}

        <span class="status">勤務外</span>
    </div>
    <div class="datetime">
        <span class="date">2023年6月1日(木)</span>
        <span class="date">{{$date}}</span>
    </div>
    <div class="datetime">
        <span class="time">08：00</span>
        <span class="time">{{$time}}</span>
    </div>

    <div class="timestamp">
        {{-- @switch($status)
            @case(0)
            <div class="button">
                <button class="button-common clockin">出 勤</button>
            </div>
                @break
            @case(1)
            <div class="button">
                <button class="button-common clockout">退 勤</button>
            </div>
            <div class="button">
                <button class="button-common rest">休 憩 入</button>
            </div>
                @break
            @case(2)
            <div class="button">
                <button class="button-common resume">休 憩 戻</button>
            </div>
                @break
            @case(3)
            <span class="message">お疲れ様でした。</span>
                @break
        @endswitch --}}

        <div class="button">
            <button class="button-common clockin">出 勤</button>
        </div>






    </div>
</div>

@endsection
