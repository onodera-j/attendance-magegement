@extends('layouts.app_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}" />
@endsection

@section('content')
    <div class="content">
        <div class="content-title">
            <h2 class="title">勤怠一覧アドミン</h2>
        </div>

        <div class="select-month">
            <div class="last-month">
                <a class="link-date"
                    href="{{ route('admin.staff.attendance.list', ['year' => $viewDate['prevYear'], 'month' => $viewDate['prevMonth'], 'id' => $user['id']]) }}">前月</a>
            </div>
            <div class="this-month">
                <img class="icon-calender" src="{{ asset('img/calender.svg') }}"><span
                    class="date">{{ $viewDate['yearMonth'] }}</span>
            </div>
            <div class="next-month">
                <a class="link-date"
                    href="{{ route('admin.staff.attendance.list', ['year' => $viewDate['nextYear'], 'month' => $viewDate['nextMonth'], 'id' => $user['id']]) }}">翌月</a>
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

                @foreach ($attendanceData as $record)
                    <tr class="table-row">
                        <td class="table-data">{{ $record['date'] }}</td>
                        <td class="table-data">{{ $record['atWork'] }}</td>
                        <td class="table-data">{{ $record['leavingWork'] }}</td>
                        <td class="table-data">{{ $record['restTime'] }}</td>
                        <td class="table-data">{{ $record['totalTime'] }}</td>
                        @if ($record['attendanceId'] != null)
                            <td class="table-data td-detail">
                                <a class="link-detail" href="/attendance/{{ $record['attendanceId'] }}">詳細</a>
                            </td>
                        @else
                            <td class="table-data td-detail"></td>
                        @endif

                    </tr>
                @endforeach
            </table>
        </div>
        <div class="csv-button">
            <form method="POST" action="{{ route('admin.export_csv', request()->query()) }}">
                @csrf
                <input type="hidden" name="baseDate" value="{{ $viewDate['baseDate'] }}">
                <input type="hidden" name="user" value="{{ $user['id'] }}">
                <button type="submit" class="button-modify">CSV出力</button>
            </form>
        </div>






    </div>
@endsection
