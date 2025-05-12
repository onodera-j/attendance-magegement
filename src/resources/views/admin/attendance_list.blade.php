@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/admin_list.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="content-title">
        <h2 class="title">2023年6月1日の勤怠</h2>
    </div>

    <div class="select-month">
        <div class="last-day">
            前日
        </div>
        <div class="this-day">
            <img class="icon-calender" src="{{ asset("img/calender.svg") }}"><span class="date">2023/06/01</span>
        </div>
        <div class="next-day">
            翌日
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
            <tr class="table-row">
                <td class="table-data">山田 太郎</td>
                <td class="table-data">09:00</td>
                <td class="table-data">18:00</td>
                <td class="table-data">1:00</td>
                <td class="table-data">8:00</td>
                <td class="table-data td-detail">詳細</td>
            </tr>
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
