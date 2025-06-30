@extends('layouts.app_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_list.css') }}" />
@endsection

@section('content')
    <div class="content">
        <div class="content-title">
            <h2 class="title">スタッフ一覧</h2>
        </div>



        <div class="attendance-list">
            <table class="table-monthly">
                <tr class="table-row">
                    <th class="table-header th-date">名前</th>
                    <th class="table-header">メールアドレス</th>
                    <th class="table-header">月次勤怠</th>

                </tr>
                @foreach ($users as $user)
                    <tr class="table-row">
                        <td class="table-data">{{ $user['name'] }}</td>
                        <td class="table-data">{{ $user['email'] }}</td>
                        <td class="table-data td-detail">
                            <a class="link-detail" href="/admin/attendance/staff/{{ $user->id }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
                <tr class="table-row">
                    <td class="table-data">戸田 一郎</td>
                    <td class="table-data">09:00</td>
                    <td class="table-data td-detail">詳細</td>
                </tr>
                <tr class="table-row">
                    <td class="table-data">青木 一太</td>
                    <td class="table-data">09:00</td>
                    <td class="table-data td-detail">詳細</td>
                </tr>
            </table>
        </div>






    </div>
@endsection
