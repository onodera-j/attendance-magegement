@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/request_list.css') }}" />
@endsection

@section('content')

    <div class="content">
        <div class="content-title">
            <h2 class="title">申請一覧</h2>
        </div>

        <div class="tab-group">
            <div class="tab-content">
                <a href="list">
                    @if ($tab === 'request')
                        <button type="submit" class="tab-button">
                            <span class="tab-bold">承認待ち</span>
                        </button>
                    @else
                        <button type="submit" class="tab-button">
                            承認待ち
                        </button>
                    @endif
                </a>
            </div>

            <form method="get" action="/stamp_correction_request/list">
                <div class="tab-content">
                    <input type="hidden" name="tab" value="approve">
                    <a href="stamp_correction_request/list">
                        @if ($tab === 'approve')
                            <button type="submit" class="tab-button">
                                <span class="tab-bold">承認済み</span>
                            </button>
                        @else
                            <button type="submit" class="tab-button">
                                承認済み
                            </button>
                        @endif
                    </a>
                </div>
            </form>
        </div>

        @switch($tab)
            @case('request')
                <div class="request-list">
                    <table class="table-request">
                        <tr class="table-row">
                            <th class="table-header th-status">状態</th>
                            <th class="table-header">名前</th>
                            <th class="table-header">対象日時</th>
                            <th class="table-header">申請理由</th>
                            <th class="table-header">申請日時</th>
                            <th class="table-header">詳細</th>
                        </tr>
                        @foreach ($requestDatas as $requestData)
                            <tr class="table-row">
                                <td class="table-data">承認待ち</td>
                                <td class="table-data">{{ $user['name'] }}</td>
                                <td class="table-data">{{ $requestData->attendance->work_start_datetime->format('Y/m/d') }}</td>
                                <td class="table-data">{{ $requestData['remarks'] }}</td>
                                <td class="table-data">{{ $requestData->requested_at->format('Y/m/d') }}</td>
                                <td class="table-data td-detail"><a class="link-detail" href="/attendance/{{ $requestData['attendance_id'] }}">詳細</a></td>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @break

            @case('approve')
                <div class="approve-list">
                    <table class="table-approve">
                        <tr class="table-row">
                            <th class="table-header th-status">状態</th>
                            <th class="table-header">名前</th>
                            <th class="table-header">対象日時</th>
                            <th class="table-header">申請理由</th>
                            <th class="table-header">申請日時</th>
                            <th class="table-header">詳細</th>
                        </tr>
                        @foreach ($approveDatas as $approveData)
                            <tr class="table-row">
                                <td class="table-data">承認済み</td>
                                <td class="table-data">{{ $user['name'] }}</td>
                                <td class="table-data">{{ $approveData->attendance->work_start_datetime->format('Y/m/d') }}</td>
                                <td class="table-data">{{ $approveData['remarks'] }}</td>
                                <td class="table-data">{{ $approveData->requested_at->format('Y/m/d') }}</td>
                                <td class="table-data td-detail"><a class="link-detail"
                                        href="/attendance/{{ $approveData['attendance_id'] }}">詳細</a></td>
                                </td>
                            </tr>
                        @endforeach

                    </table>
                </div>
            @break
        @endswitch






    </div>


@endsection
