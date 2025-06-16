@extends('layouts.app_admin')

@section("css")
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}" />
@endsection

@section("content")

<div class="content">
    <div class="content-title">
        <h2 class="title">勤怠詳細</h2>
    </div>

    <form method="POST" action="/request/approve">
        @csrf
    <div class="attendance-detail">
        <div class="detail-row">
            <div class="row-header">名前</div>
            <div class="row-content content-record name">{{$requestData->user->name}}</div>
        </div>
        <div class="detail-row">
            <div class="row-header">日付</div>
            <div class="row-content content-record text-datetime">{{$requestData->work_start_datetime->format('Y年')}}</div>
            <div class="row-content content-decorate"></div>
            <div class="row-content content-record text-datetime">{{$requestData->work_start_datetime->isoformat('M月D日')}}</div>
        </div>
        <div class="detail-row">
            <div class="row-header">出勤・退勤</div>
            <div class="row-content content-record text-datetime">{{$requestData->work_start_datetime->format('H:i')}}</div>
            <div class="row-content content-decorate">～</div>
            <div class="row-content content-record text-datetime">{{$requestData->work_end_datetime->format('H:i')}}</div>
            <input type="hidden" name="requestId" value="{{$requestData->id}}">
        </div>
        @foreach($requestRestDatas as $index => $requestRestData)
        <div class="detail-row">
            <div class="row-header">休憩{{$loop->iteration}}</div>
            <div class="row-content content-record text-datetime">{{$requestRestData->rest_start_datetime ? $requestRestData->rest_start_datetime->format('H:i') : ""}}</div>
            <div class="row-content content-decorate">～</div>
            <div class="row-content content-record text-datetime">{{$requestRestData->rest_end_datetime ?$requestRestData->rest_end_datetime->format('H:i') : ""}}</div>
            <input type="hidden" name="requestRestId[{{$index}}]" value="{{$requestRestData->id}}">
        </div>
        @endforeach
        <div class="detail-row">
            <div class="row-header">備考</div>
            <div class="row-content content-remarks text-remarks">{{$requestData->remarks}}</div>
        </div>

    </div>
    @switch($approveStatus)
        @case(0)
            <div class="form-button">
                <button class="button-modify">承認</button>
            </div>

        @break
        @case(1)
            <div class="form-button">
                <button class="button-approved">承認済み</button>
            </div>
        @break
    @endswitch
    </form>


</div>


@endsection
