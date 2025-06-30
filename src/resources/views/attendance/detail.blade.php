@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}" />
@endsection

@section('content')

    <div class="content">
        <div class="content-title">
            <h2 class="title">勤怠詳細</h2>
        </div>
        @switch($attendanceData["pending"])
            @case(0)
                <form method="POST" action="/stamp_correction_request">
                    @csrf
                    <div class="attendance-detail">
                        <div class="detail-row">
                            <div class="row-header">名前</div>
                            <div class="row-content content-record name">{{ $user->name }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="row-header">日付</div>
                            <div class="row-content content-record date">{{ $attendanceData->work_start_datetime->format('Y年') }}
                            </div>
                            <div class="row-content content-decorate"></div>
                            <div class="row-content content-record date">
                                {{ $attendanceData->work_start_datetime->isoformat('M月D日') }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="row-header">出勤・退勤</div>
                            <div class="row-content content-record"><input class="record" type="text" name="work_start"
                                    value="{{ old('work_start', $attendanceData->work_start_datetime->format('H:i')) }}"></div>
                            <div class="row-content content-decorate">～</div>
                            <div class="row-content content-record"><input class="record" type="text" name="work_end"
                                    value="{{ old('work_end', $attendanceData->work_end_datetime->format('H:i')) }}"></div>
                            <div class="row-content content-error">
                                @error('work_start')
                                    {{ $message }}
                                @enderror
                                @error('work_end')
                                    {{ $message }}
                                @enderror
                            </div>

                            <input type="hidden" name="attendance_id" value="{{ $attendanceData->id }}">
                            <input type='hidden' name="work_start_date"
                                value="{{ $attendanceData->work_start_datetime->format('Y-m-d') }}">
                            <input type='hidden' name="work_end_date"
                                value="{{ $attendanceData->work_end_datetime->format('Y-m-d') }}">
                        </div>
                        @foreach ($restDatas as $index => $restData)
                            <div class="detail-row">
                                <div class="row-header">休憩{{ $loop->iteration }}</div>
                                <div class="row-content content-record"><input class="record" type="text"
                                        name="rest_start[{{ $index }}]"
                                        value="{{ old('rest_start' . $index, $restData->rest_start_datetime ? $restData->rest_start_datetime->format('H:i') : '') }}">
                                </div>
                                <div class="row-content content-decorate">～</div>
                                <div class="row-content content-record"><input class="record" type="text"
                                        name="rest_end[{{ $index }}]"
                                        value="{{ old('rest_end' . $index, $restData->rest_end_datetime ? $restData->rest_end_datetime->format('H:i') : '') }}">
                                </div>
                                <div class="row-content content-error">
                                    @error('rest_start.*')
                                        {{ $message }}
                                    @enderror
                                    @error('rest_end.*')
                                        {{ $message }}
                                    @enderror
                                </div>
                                <input type="hidden" name="rest_id[{{ $index }}]" value="{{ $restData->id ?? '' }}">
                                <input type='hidden' name="rest_start_date[{{ $index }}]"
                                    value="{{ $restData->rest_start_datetime ? $restData->rest_start_datetime->format('Y-m-d') : '' }}">
                                <input type='hidden' name="rest_end_date[{{ $index }}]"
                                    value="{{ $restData->rest_end_datetime ? $restData->rest_end_datetime->format('Y-m-d') : '' }}">
                            </div>
                        @endforeach
                        <div class="detail-row">
                            <div class="row-header">備考</div>
                            <div class="row-content content-remarks">
                                <textarea class="remarks" name="remarks">{{ $attendanceData->remarks ?? '' }}</textarea>
                            </div>
                            <div class="row-content content-error">
                                @error('remarks')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-button">
                        <button class="button-modify">修正</button>
                    </div>
                </form>
            @break

            @case(1)
                <div class="attendance-detail">
                    <div class="detail-row">
                        <div class="row-header">名前</div>
                        <div class="row-content content-record name">{{ $user->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="row-header">日付</div>
                        <div class="row-content content-record text-datetime">{{ $requestData->work_start_datetime->format('Y年') }}
                        </div>
                        <div class="row-content content-decorate"></div>
                        <div class="row-content content-record text-datetime">
                            {{ $requestData->work_start_datetime->isoformat('M月D日') }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="row-header">出勤・退勤</div>
                        <div class="row-content content-record text-datetime">
                            {{ $requestData->work_start_datetime->format('H:i') }}</div>
                        <div class="row-content content-decorate">～</div>
                        <div class="row-content content-record text-datetime">{{ $requestData->work_end_datetime->format('H:i') }}
                        </div>
                    </div>
                    @foreach ($requestRestDatas as $index => $requestRestData)
                        <div class="detail-row">
                            <div class="row-header">休憩{{ $loop->iteration }}</div>
                            <div class="row-content content-record text-datetime">
                                {{ $requestRestData->rest_start_datetime ? $requestRestData->rest_start_datetime->format('H:i') : '' }}
                            </div>
                            <div class="row-content content-decorate">～</div>
                            <div class="row-content content-record text-datetime">
                                {{ $requestRestData->rest_end_datetime ? $requestRestData->rest_end_datetime->format('H:i') : '' }}
                            </div>
                        </div>
                    @endforeach
                    <div class="detail-row">
                        <div class="row-header">備考</div>
                        <div class="row-content content-remarks text-remarks">{{ $requestData->remarks }}</div>
                    </div>
                </div>
                <div class="message-diseditable">
                    *承認待ちのため修正はできません。
                </div>
            @break
        @endswitch

    </div>


@endsection
