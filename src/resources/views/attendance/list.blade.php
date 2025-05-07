@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="content-title">
        <h2 class="title">勤怠一覧</h2>
    </div>

    <div class="select-month">
        <div class="last-month">
            前月
        </div>
        <div class="this-month">
            <img class="icon-calender" src="{{ asset("img/calender.svg") }}"><span class="date">2023/06</span>
        </div>
        <div class="next-month">
            翌月
        </div>
    </div>

    <div class="attendance-list">
        <table class="table-monthly">
            <tr class="table-row">
                <th class="table-header th-date">日付</th>
                <th class="table-header">出勤</th>
                <th class="table-header">退勤</th>
                <th class="table-header">休憩</th>
                <th class="table-header">合計</th>
                <th class="table-header">詳細</th>
            </tr>
            <tr class="table-row">
                <td class="table-data">06/01(木)</td>
                <td class="table-data">09:00</td>
                <td class="table-data">18:00</td>
                <td class="table-data">1:00</td>
                <td class="table-data">8:00</td>
                <td class="table-data td-detail">詳細</td>
            </tr>
            <tr class="table-row">
                <td class="table-data">06/02(金)</td>
                <td class="table-data">09:00</td>
                <td class="table-data">18:00</td>
                <td class="table-data">1:00</td>
                <td class="table-data">8:00</td>
                <td class="table-data td-detail">詳細</td>
            </tr>
            <tr class="table-row">
                <td class="table-data">06/03(土)</td>
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
