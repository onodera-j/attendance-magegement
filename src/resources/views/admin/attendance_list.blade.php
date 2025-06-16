@extends('layouts.app_admin')

@section("css")
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="content-title">
        <h2 class="title">{{$viewDates['titleDate']->isoformat('Y年M月D日')}}の勤怠</h2>
    </div>

    <div class="select-month">
        <div class="last-day">
            <a class="link-date" href="{{ route('admin.attendance.list', ['year' => $viewDates['prevYear'], 'month' => $viewDates['prevMonth'], 'day' => $viewDates['prevDay']]) }}">前日</a>
        </div>
        <div class="this-day">
            <img class="icon-calender" src="{{ asset("img/calender.svg") }}"><span class="date">{{$viewDates['titleDate']->format('Y/m/d')}}</span>
        </div>
        <div class="next-day">
            <a class="link-date" href="{{ route('admin.attendance.list', ['year' => $viewDates['nextYear'], 'month' => $viewDates['nextMonth'], 'day' => $viewDates['nextDay']]) }}">翌日</a>
        </div>
    </div>

    <div class="attendance-list">
        <table class="table-monthly">
            <tr class="table-row">
                <th class="table-header th-date">名前</th>
                <th class="table-header">出勤</th>
                <th class="table-header">退勤</th>
                <th class="table-header">休憩</th>
                <th class="table-header">合計</th>
                <th class="table-header">詳細</th>
            </tr>
            @foreach($attendanceDatas as $attendanceData)
            <tr class="table-row">
                <td class="table-data">{{$attendanceData->user->name}}</td>
                <td class="table-data">{{$attendanceData->work_start_datetime->format('H:i')}}</td>
                <td class="table-data">
                    @if ($attendanceData->work_end_datetime)
                        {{$attendanceData->work_end_datetime->format('H:i')}}
                    @endif
                </td>
                <td class="table-data">{{$attendanceData->restTime}}</td>
                <td class="table-data">{{$attendanceData->totalTime}}</td>
                <td class="table-data td-detail">
                    <a class="link-detail" href="/attendance/{{$attendanceData->id}}">詳細</a></td>
            </tr>
            @endforeach
            <tr class="table-row">
                <td class="table-data">戸田 一郎</td>
                <td class="table-data">09:00</td>
                <td class="table-data">18:00</td>
                <td class="table-data">1:00</td>
                <td class="table-data">8:00</td>
                <td class="table-data td-detail">詳細</td>
            </tr>
            <tr class="table-row">
                <td class="table-data">青木 一太</td>
                <td class="table-data">09:00</td>
                <td class="table-data">18:00</td>
                <td class="table-data">1:00</td>
                <td class="table-data">8:00</td>
                <td class="table-data td-detail">詳細</td>
            </tr>
        </table>
    </div>






</div>


@endsection
