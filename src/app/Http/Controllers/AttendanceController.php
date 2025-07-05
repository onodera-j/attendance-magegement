<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use App\Models\Request as UserRequest;
use App\Models\RestRequest;
use App\Http\Requests\RequestRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function attendance()
    {

        $user = Auth::user();
        $status = Auth::user()->status;
        $now = Carbon::now();
        $date = $now->isoformat('Y年M月D日(ddd)');
        $time = $now->format('H：i');

        $attendanceExists = Attendance::whereDate('work_start_datetime',$now->format('Y-m-d'))
                                ->where('user_id',$user->id)
                                ->exists();

        if($attendanceExists && $user->status === 0){
            $status = 3;
        }

        return view('attendance.attendance', compact('date','time','user','status'));
    }

    public function atWork()
    {
        // DB::beginTransaction();
        // try{
            $user = Auth::user();
            $now = Carbon::now();
            $attendanceData['user_id'] = $user->id;
            $attendanceData['work_start_datetime'] = $now;

            Attendance::create($attendanceData);

            $person = User::find($user->id);
            $person->update(['status' => 1]);

            DB::commit();

            return redirect('/attendance');

    }

    public function leavingWork()
    {
        DB::beginTransaction();
        try{
            $user = Auth::user();
            $now = Carbon::now();

            $attendanceRecord = Attendance::where('work_end_datetime', null)
                                    ->where('user_id',$user->id)->first();
            $attendanceData['work_end_datetime'] = $now;

            $workStart = Carbon::parse($attendanceRecord->work_start_datetime)->startOfSecond();
            $workEnd = $now->startOfSecond();
            $attendanceData['work_time'] = $workEnd->diffInMinutes($workStart);

            $attendanceRecord->update($attendanceData);

            $person = User::find($user->id);
            $person->update(["status" => 0]);

            DB::commit();

            return redirect('/attendance');

        }catch(\Exception $e) {
            DB::rollback();
            Log::error("Error: " . $e->getMessage());
            return back()->withErrors(["error", "エラーが発生しました"]);
        }
    }

    public function atBreak()
    {
        DB::beginTransaction();
        try{
            $user = Auth::user();
            $now = Carbon::now();
            $restData['user_id'] = $user->id;
            $attendance = Attendance::where('work_end_datetime', null)
                                ->where('user_id',$user->id)->first();
            $restData['attendance_id'] = $attendance->id;
            $restData['rest_start_datetime'] = $now;
            Rest::create($restData);

            $person = User::find($user->id);
            $person->update(["status" => 2]);

            DB::commit();

            return redirect('/attendance');

        }catch(\Exception $e) {
            DB::rollback();
            Log::error("Error: " . $e->getMessage());
            return back()->withErrors(["error", "エラーが発生しました"]);
        }
    }

    public function leavingBreak()
    {
        DB::beginTransaction();
        try{
            $user = Auth::user();
            $now = Carbon::now();
            $restRecord = Rest::where('rest_status', 1)
                                ->where('user_id',$user->id)
                                ->where('rest_end_datetime', null)->first();
            $restRecord->rest_end_datetime = $now;

            $restStart = Carbon::parse($restRecord->rest_start_datetime)->startOfSecond();
            $restEnd = $now->startOfSecond();
            $restRecord->rest_time = $restEnd->diffInMinutes($restStart);

            $restRecord->rest_status = 0;
            $restRecord->save();

            $person = User::find($user->id);
            $person->update(["status" => 1]);

            DB::commit();

            return redirect('/attendance');

        }catch(\Exception $e) {
            DB::rollback();
            Log::error("Error: " . $e->getMessage());
            return back()->withErrors(["error", "エラーが発生しました"]);
        }


    }



    public function attendanceList(Request $request)
    {
        $user = Auth::user();

        // 表示する年月を設定 (デフォルトは現在の年月)
        $year = $request->query('year') ?? Carbon::now()->year;
        $month = $request->query('month') ?? Carbon::now()->month;

        $baseDate = Carbon::create($year, $month,1);
        $prevMonthDate = $baseDate->copy()->subMonth();
        $nextMonthDate = $baseDate->copy()->addMonth();

        $viewDate = [];
        $viewDate = [
            'yearMonth' => $baseDate->format('Y/m'),
            'prevYear' => $prevMonthDate->year,
            'prevMonth' => $prevMonthDate->month,
            'nextYear' => $nextMonthDate->year,
            'nextMonth' => $nextMonthDate->month,
        ];

        // 指定された年月の最終日を取得
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $attendanceData = [];

        for($day = 1; $day <= $daysInMonth; $day++) {

            $currentDate = Carbon::create($year, $month, $day)->startOfDay();
            $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_start_datetime', $currentDate)
                                ->first();

            $date = $currentDate -> isoformat('MM/DD(ddd)');
            $atWork = '';
            $leavingWork = '';
            $restTime = '';
            $totalTime = '';
            $attendanceId = null;

            if($attendance) {

                $rests = Rest::where('attendance_id', $attendance->id)->get();
                $totalRestTimeMinutes = $rests->sum('rest_time') ?? 0;

                $atWork = $attendance->work_start_datetime ? Carbon::parse($attendance->work_start_datetime)->format('H:i') : '';
                $leavingWork = $attendance->work_end_datetime ? Carbon::parse($attendance->work_end_datetime)->format('H:i') : '';

                $restHours = floor($totalRestTimeMinutes / 60);
                $restMinutes = $totalRestTimeMinutes % 60;
                $restTime = sprintf('%2d:%02d', $restHours, $restMinutes);

                $totalTimeMinutes = $attendance->work_time - $totalRestTimeMinutes ?? 0;

                $totalHours = floor($totalTimeMinutes / 60);
                $totalMinutes = $totalTimeMinutes % 60;
                $totalTime = sprintf('%2d:%02d', $totalHours, $totalMinutes);
                $attendanceId = $attendance->id;

            }

            $attendanceData[] = [
                'date' => $date,
                'atWork' => $atWork,
                'leavingWork' => $leavingWork,
                'restTime' => $restTime,
                'totalTime' => $totalTime,
                'attendanceId' => $attendanceId,
            ];


        }

        return view('attendance.list', compact('attendanceData', 'viewDate',));
    }

    public function attendanceDetail(Request $request,$id)
    {
        $user = Auth::user();

        $attendanceData = [];
        $attendanceData = Attendance::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->first();

        $restDatas = Rest::where('user_id', $user->id)
                        ->where('attendance_id', $attendanceData->id)->get();
        $requestData = [];
        $requestRestDatas = [];
        if($attendanceData->pending === 1){
            $requestData = UserRequest::where('user_id', $user->id)
                                    ->where('approve_status', 0)
                                    ->where('attendance_id', $id)->first();
            $requestRestDatas = RestRequest::where('request_id',  $requestData->id)->get();
        }

        $newEmptyRest = new Rest([
            'rest_start_datetime' => null,
            'rest_end_datetime' => null,
            'id' => null,
        ]);

        $restDatas = $restDatas->push($newEmptyRest);


        return view('attendance.detail', compact('user','attendanceData','restDatas', 'id', "requestData", "requestRestDatas"));
    }



    public function stampCorrectionRequest(RequestRequest $request){
        DB::beginTransaction();
        try{
            $user = Auth::user();
            $attendanceId = $request->input("attendance_id");
            $attendanceData = Attendance::where('user_id', $user->id)
                                    ->where('id', $attendanceId)->first();
            //attendanceレコードの更新
            $attendanceData["pending"] = 1;
            $attendanceData->save();

            //requestテーブルに新規追加
            $work_start_date = $request->input("work_start_date");
            $work_start_time = $request->input("work_start");
            $work_end_date = $request->input("work_end_date");
            $work_end_time = $request->input("work_end");

            $work_start_datetime = Carbon::parse($work_start_date . " " . $work_start_time);
            $work_end_datetime = Carbon::parse($work_end_date . " " . $work_end_time);

            $requestData = [
                "user_id" => $user->id,
                "attendance_id" => $attendanceId,
                "work_start_datetime" => $work_start_datetime,
                "work_end_datetime" => $work_end_datetime,
                "remarks" => $request->input("remarks"),
                "requested_at" => Carbon::now(),
                "approve_status" => 0,
            ];

            $createdUserRequest = UserRequest::create($requestData);
            $newRequestId = $createdUserRequest->id;

            //restrequestに追加

            $restStartsDates = $request->input('rest_start_date',[]);
            $restStarts = $request->input('rest_start',[]);
            $restEndsDates = $request->input('rest_end_date',[]);
            $restEnds = $request->input('rest_end',[]);
            $restIds = $request->input('rest_id',[]);

            foreach ($restIds as $index => $restIdValue) {
                $restStartStr = $restStarts[$index] ?? null;
                $restEndStr = $restEnds[$index] ?? null;
                $restStartDateStr = $restStartsDates[$index] ?? null;
                $restEndDateStr = $restEndsDates[$index] ?? null;

                if(!$restIdValue && !$restStartStr && !$restEndStr){
                    continue;
                }

                if($restIdValue && !$restStartStr && !$restEndStr){
                    RestRequest::create([
                        "request_id" => $newRequestId,
                        "rest_id" => $restIdValue,
                        "rest_start_datetime" => null,
                        "rest_end_datetime" => null,
                    ]);
                }elseif($restIdValue && $restStartStr && $restEndStr && $restStartDateStr && $restEndDateStr) {
                    $restStartDatetime = $restStartDateStr . " " . $restStartStr;
                    $restEndDatetime = $restEndDateStr . " " . $restEndStr;

                    RestRequest::create([
                        "request_id" => $newRequestId,
                        "rest_id" => $restIdValue,
                        "rest_start_datetime" => $restStartDatetime,
                        "rest_end_datetime" => $restEndDatetime,
                    ]);
                }elseif(!$restIdValue && $restStartStr && $restEndStr){
                    //新規で休憩時間を登録
                    $restStartDatetime = $work_start_date . " " . $restStartStr;
                    $restEndDatetime = $work_start_date . " " . $restEndStr;

                    RestRequest::create([
                        "request_id" => $newRequestId,
                        "rest_id" => $restIdValue,
                        "rest_start_datetime" => $restStartDatetime,
                        "rest_end_datetime" => $restEndDatetime,
                    ]);

                }else{
                    continue;
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "申請しました");
        }catch(\Exception $e) {
            DB::rollback();
            Log::error("Error: " . $e->getMessage());
            dd("Error occurred: " . $e->getMessage(), $e->getTraceAsString());
        // Log::error("Error: " . $e->getMessage()); // これは残しても良い
            return back()->withErrors(["error", "エラーが発生しました"]);
        }
    }

    public function requestList(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query("tab", "request");

        $requestDatas = [];
        $approveDatas = [];

        if($tab === "request"){
            $requestDatas = UserRequest::where('user_id',$user->id)
                            ->where('approve_status', 0)
                            ->get();
        }

        if($tab === "approve"){
            $approveDatas = UserRequest::where('user_id',$user->id)
                            ->where('approve_status', 1)
                            ->get();
        }


        return view('stamp_correction_request.list', compact('user','tab', 'requestDatas', 'approveDatas'));
    }

}
