@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
    <div class="content">
        <div class="user-status">
            @switch($status)
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
            @endswitch

        </div>
        <div class="datetime">
            <span class="date">{{ $date }}</span>
        </div>
        <div class="datetime">
            <span class="time">{{ $time }}</span>
        </div>

        <div class="timestamp">
            @switch($status)
                @case(0)
                    <form method="POST" action='/at_work'>
                        @csrf
                        <div class="button">
                            <button class="button-common clockin">出勤</button>
                        </div>
                    </form>
                @break

                @case(1)
                    <form method="POST" action="/leaving_work">
                        @csrf
                        <div class="button">
                            <button class="button-common clockout">退勤</button>
                        </div>
                    </form>
                    <form method="POST" action="/at_break">
                        @csrf
                        <div class="button">
                            <button class="button-common rest">休憩入</button>
                        </div>
                    </form>
                @break

                @case(2)
                    <form method="POST" action="/leaving_break">
                        @csrf
                        <div class="button">
                            <button class="button-common resume">休憩戻</button>
                        </div>
                    </form>
                @break

                @case(3)
                    <span class="message">お疲れ様でした。</span>
                @break
            @endswitch


        </div>
    </div>
@endsection
