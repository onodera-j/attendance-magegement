<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request as UserRequest;
use App\Models\RestRequest;
use App\Http\Requests\adminCorrectionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function attendanceList(Request $request)
    {

        $year = $request->query('year') ?? Carbon::now()->year;
        $month = $request->query('month') ?? Carbon::now()->month;
        $day = $request->query('day') ?? Carbon::now()->day;

        $baseDate = Carbon::create($year, $month,$day);
        $prevDayDate = $baseDate->copy()->subDay();
        $nextDayDate = $baseDate->copy()->addDay();

        $viewDates = [];
        $viewDates = [
            'titleDate' => $baseDate,
            'yearMonthDay' => $baseDate->format('Y/m/d'),
            'prevYear' => $prevDayDate->year,
            'prevMonth' => $prevDayDate->month,
            'prevDay' => $prevDayDate->day,
            'nextYear' => $nextDayDate->year,
            'nextMonth' => $nextDayDate->month,
            'nextDay' => $nextDayDate->day,
        ];

        $attendanceDatas = [];

        $attendanceDatas = Attendance::whereDate('work_start_datetime', $baseDate)
        ->with('rest')->get();

        foreach($attendanceDatas as $attendanceData) {
            $restDatas = Rest::where('attendance_id', $attendanceData->id)
                                ->get();
            $totalRestTimeMinutes = $attendanceData->rest->sum('rest_time') ?? 0;

            $restHours = floor($totalRestTimeMinutes / 60);
            $restMinutes = $totalRestTimeMinutes % 60;
            $formatRestTime = sprintf('%2d:%02d', $restHours, $restMinutes);

            $totalTimeMinutes = $attendanceData->work_time - $totalRestTimeMinutes;

            $totalHours = floor($totalTimeMinutes / 60);
            $totalMinutes = $totalTimeMinutes % 60;
            $formatTotalTime = sprintf('%2d:%02d', $totalHours, $totalMinutes);

            $attendanceData->restTime = $formatRestTime;
            $attendanceData->totalTime = $formatTotalTime;
        }

        return view('admin.attendance_list', compact('viewDates','attendanceDatas'));
    }

    public function staffList(Request $request)
    {
        $users = User::select("id","name","email")->get();

        return view('admin.staff_list', compact('users'));
    }

    public function attendanceDetail(Request $request, $id)
    {

        $attendanceData = Attendance::with('user', 'rest')->findOrFail($id);

        $restDatas = $attendanceData->rest;

        $requestData = [];
        $requestRestDatas = [];

        $newEmptyRest = new Rest([
            'rest_start_datetime' => null,
            'rest_end_datetime' => null,
            'id' => null,
        ]);

        $restDatas = $restDatas->push($newEmptyRest);

        return view('admin.detail', compact('attendanceData','restDatas', 'id', "requestData", "requestRestDatas"));
    }

    public function attendanceCorrection(adminCorrectionRequest $request)
    {
        DB::beginTransaction();
        try{
            $attendanceId = $request->input("attendance_id");
            $attendanceData = Attendance::where("id", $attendanceId)->first();

            //attendancesテーブルの更新
            $work_start_date = $request->input("work_start_date");
            $work_start_time = $request->input("work_start");
            $work_end_date = $request->input("work_end_date");
            $work_end_time = $request->input("work_end");

            $work_start_datetime = Carbon::parse($work_start_date . " " . $work_start_time);
            $work_end_datetime = Carbon::parse($work_end_date . " " . $work_end_time);

            $workTime = $work_end_datetime->diffInMinutes($work_start_datetime);
            $remarks = $request->input("remarks");

            $attendanceData["work_start_datetime"] = $work_start_datetime;
            $attendanceData["work_end_datetime"] = $work_end_datetime;
            $attendanceData["work_time"] = $workTime;
            $attendanceData["remarks"] = $remarks;
            $attendanceData->save();

            //restsテーブルの更新
            $userId = $attendanceData["user_id"];
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

                if($restIdValue != null && !$restStartStr && !$restEndStr){
                    $restData = Rest::find($restIdValue);
                    $restData->delete();
                    continue;
                }

                if($restStartStr && $restEndStr){


                    if($restIdValue){

                        $restData = Rest::find($restIdValue);
                        $rest_start_datetime = Carbon::parse($restStartDateStr . " " . $restStartStr);
                        $rest_end_datetime = Carbon::parse($restEndDateStr . " " . $restEndStr);
                        $restTime = $rest_end_datetime->diffInMinutes($rest_start_datetime);

                        $restData->rest_start_datetime = $rest_start_datetime;
                        $restData->rest_end_datetime = $rest_end_datetime;
                        $restData->rest_time = $restTime;
                        $restData->save();
                    }else{

                        $rest_start_datetime = Carbon::parse($work_start_date . " " . $restStartStr);
                        $rest_end_datetime = Carbon::parse($work_start_date . " " . $restEndStr);
                        $restTime = $rest_end_datetime->diffInMinutes($rest_start_datetime);

                        Rest::create([
                            "user_id" => $userId,
                            "attendance_id" => $attendanceId,
                            "rest_start_datetime" => $rest_start_datetime,
                            "rest_end_datetime" => $rest_end_datetime,
                            "rest_time" => $restTime,
                            "rest_status" => 0,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back();

        }catch(\Exception $e) {
            DB::rollback();
            Log::error("Error: " . $e->getMessage());
            return back()->withErrors(["error", "エラーが発生しました"]);
        }
    }

    public function staffAttendanceList(Request $request, $id)
    {
        $user = User::where("id", $id)->first();

        // 表示する年月を設定 (デフォルトは現在の年月)
        $year = $request->query('year') ?? Carbon::now()->year;
        $month = $request->query('month') ?? Carbon::now()->month;

        $baseDate = Carbon::create($year, $month,1);
        $prevMonthDate = $baseDate->copy()->subMonth();
        $nextMonthDate = $baseDate->copy()->addMonth();

        $viewDate = [];
        $viewDate = [
            'baseDate' => $baseDate,
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

        return view('admin.staff_attendance_list', compact('attendanceData', 'viewDate', 'user'));
    }

    public function requestList(Request $request)
    {
        $user = "admin";
        $tab = $request->query("tab", "request");

        $requestDatas = [];
        $approveDatas = [];

        if($tab === "request"){
            $requestDatas = UserRequest::where('approve_status',0)
                            ->with('user')->get();
        }

        if($tab === "approve"){
            $approveDatas = UserRequest::where('approve_status',1)
                            ->with('user')->get();
        }


        return view('admin.stamp_correction_request_list', compact('tab', 'requestDatas', 'approveDatas', 'user'));
    }

    public function approveRequest($attendance_correct_request)
    {
        $requestData = [];
        $requestRestDatas= [];

        $requestData = UserRequest::where('id', $attendance_correct_request)
                            ->with('user', 'restrequest')->first();
        $requestRestDatas = RestRequest::where('request_id', $requestData->id)
                            ->get();
        $approveStatus = $requestData->approve_status;

        return view('stamp_correction_request.approve', compact('requestData', "requestRestDatas", "approveStatus"));
    }

    public function requestApprove(Request $request)
    {
        try{
            DB::beginTransaction();
            $approveId = $request->input('requestId');

            $approveData = UserRequest::where('id', $approveId)->first();
            $approveWorkTime = $approveData["work_end_datetime"]->diffInMinutes($approveData["work_start_datetime"]);

            //attendanceテーブルを更新
            $attendanceData = Attendance::find($approveData["attendance_id"]);

            $attendanceData->work_start_datetime = $approveData["work_start_datetime"];
            $attendanceData->work_end_datetime = $approveData["work_end_datetime"];
            $attendanceData->work_time = $approveWorkTime;
            $attendanceData->remarks = $approveData["remarks"];
            $attendanceData->pending = 0;

            $attendanceData->save();

            $requestRestIds = $request->input('requestRestId',[]);

            foreach ($requestRestIds as $index => $requestRestIdValue) {
                $requestRestData = RestRequest::find($requestRestIdValue);
                $restId = $requestRestData->rest_id;
                $restStartDatetime = $requestRestData->rest_start_datetime;
                $restEndDatetime = $requestRestData->rest_end_datetime;

                if($restStartDatetime === null && $restEndDatetime === null){
                    $restData = Rest::find($restId);
                    $restData->delete();
                    continue;
                }

                if($restStartDatetime !== null && $restEndDatetime !== null){

                    if($restId !== null){
                        $restData = Rest::find($restId);
                        $restData->rest_start_datetime = $restStartDatetime;
                        $restData->rest_end_datetime = $restEndDatetime;

                        $restTime = $restEndDatetime->diffInMinutes($restStartDatetime);
                        $restData->rest_time = $restTime;
                        $restData->rest_status = 0;                     $restData->save();

                    }else{
                        $restTime = $restEndDatetime->diffInMinutes($restStartDatetime);
                        $attendanceId = $attendanceData->id;
                        $userId = $attendanceData->user_id;

                        Rest::create([
                            "user_id" => $userId,
                            "attendance_id" => $attendanceId,
                            "rest_start_datetime" => $restStartDatetime,
                            "rest_end_datetime" => $restEndDatetime,
                            "rest_time" => $restTime,
                            "rest_status" => 0,
                        ]);
                    }
                }
            }

            //requestテーブルを更新
            $approveData["approve_status"] = 1;
            $approveData->save();

            DB::commit();
            return redirect()->back();
        }catch(\Exception $e) {
            DB::rollback();
            Log::error("Error: " . $e->getMessage());
            return back()->withErrors(["error", "エラーが発生しました"]);
        }
    }

    public function exportCsv(Request $request)
    {
        $baseDate = Carbon::parse($request->input("baseDate"));
        $userId = $request->input("user");
        $year = $baseDate->year;
        $month = $baseDate->month;
        $daysInMonth = Carbon::create($baseDate)->daysInMonth;


        $attendanceRecords = Attendance::where("user_id", $userId)
                                    ->whereYear("work_start_datetime", $year)
                                    ->whereMonth("work_start_datetime", $month)
                                    ->with("rest")
                                    ->orderBy("work_start_datetime")
                                    ->get();

        $csvHeader = [
            '日付', '出勤', '退勤', '休憩', '合計',
        ];

        $callback = function() use ($csvHeader, $attendanceRecords, $year, $month, $daysInMonth) {
            $file = fopen('php://output', 'w');

                fputcsv($file, $csvHeader);

            for($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::create($year, $month, $day)->startOfDay();
                $attendance = $attendanceRecords->first(function($record) use ($currentDate) {
                    return $record->work_start_datetime->toDateString() === $currentDate->toDateString();
            });

            $date = $currentDate -> isoformat('MM/DD(ddd)');
            $atWork = '';
            $leavingWork = '';
            $restTime = '';
            $totalTime = '';

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

            fputcsv($file, [
                $date,
                $atWork,
                $leavingWork,
                $restTime,
                $totalTime,
            ]);
        }
        fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8', // 文字コードをUTF-8に指定
            'Content-Disposition' => 'attachment; filename="attendance_report_' . $baseDate->format('Ym') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
