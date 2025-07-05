<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserAttendanceSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request as UserRequest;
use Carbon\Carbon;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserAttendanceSeeder::class);

        Carbon::setTestNow(Carbon::now());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    //4 日時取得機能
    public function test_attendance_日時取得()
    {

        $user = User::find(1);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $now = Carbon::now();
        $date = $now->isoformat('Y年M月D日(ddd)');
        $time = $now->format('H：i');

        $response->assertSee($date);
        $response->assertSee($time);

    }

    //5 ステータス確認機能
    public function test_attendance_ステータス()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $now = Carbon::now();
        Carbon::setTestNow($now);

        //勤務外ステータス確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $this->assertEquals(0,$user->status);

        $response = $this->post('/at_work');
        $response->assertStatus(302);
        $user->refresh();
        $this->assertEquals(1,$user->status);

        //勤務中ステータス確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        $response = $this->post('/at_break');
        $response->assertStatus(302);
        $user->refresh();
        $this->assertEquals(2,$user->status);

        //休憩中ステータス確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');

        $response = $this->post('/leaving_break');
        $response->assertStatus(302);
        $user->refresh();
        $this->assertEquals(1,$user->status);
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $response = $this->post('/leaving_work');
        $response->assertStatus(302);
        $user->refresh();
        $this->assertEquals(0,$user->status);

        //退勤済ステータス
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
        $response->assertStatus(200);
    }

    //6 出勤機能
    public function test_attendance_出勤()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $now = Carbon::now();

        $response = $this->post('/at_work');
        $response->assertStatus(302);
        $user->refresh();

        //勤怠一覧画面
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        //期待値
        $expectedDate = $now->copy()->isoformat("MM/DD(ddd)");
        $searchDate = $now->copy()->format('Y-m-d');
        $attendanceData = Attendance::where('user_id', $user->id)
                            ->whereDate("work_start_datetime", $searchDate)
                            ->first();
        $expectedWorkStartTime = $attendanceData->work_start_datetime->format("H:i");

        $response->assertSee($expectedDate);
        $response->assertSee($expectedWorkStartTime);

        //退勤処理
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/leaving_work');
        $response->assertStatus(302);
        $user->refresh();

        //出勤ボタンは押せない
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');

    }

    //7 休憩機能
    public function test_attendance_休憩()
    {
        $user = User::factory()->create(["status" => 1]);
        $now = Carbon::now();

        Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $now,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        //休憩1回目
        $response = $this->post('/at_break');
        $response->assertStatus(302);
        $user->refresh();
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        //休憩1回目終了
        $response = $this->post('/leaving_break');
        $response->assertStatus(302);
        $user->refresh();
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        //休憩2回目
        $response = $this->post('/at_break');
        $response->assertStatus(302);
        $user->refresh();
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        $response = $this->post('/leaving_break');
        $response->assertStatus(302);
        $user->refresh();
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        //管理画面表示　期待値作成
        $date = $now->format("Y-m-d");
        $restDatas = Rest::where('user_id', $user->id)
                        ->whereDate('rest_start_datetime', $date)
                        ->get();
        //テストで作成した休憩データが2件あることを確認
        $this->assertEquals(2, $restDatas->count());
        $restTotalTime = $restDatas->sum('rest_time');
        $restHours = floor($restTotalTime / 60);
        $restMinutes = $restTotalTime % 60;

        $expectedDate = $now->isoformat("MM/DD(ddd)");
        $expectedTotalRestTime = sprintf('%2d:%02d', $restHours, $restMinutes);

        //管理画面で確認
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTotalRestTime);

    }

    //8 退勤機能
    public function test_attendance_退勤()
    {
        $user = User::factory()->create(["status" => 1]);
        $now = Carbon::now();

        Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $now,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('退勤');

        //退勤処理
        $response = $this->post('/leaving_work');
        $response->assertStatus(302);
        $user->refresh();
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');

        //勤怠一覧画面
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        //退勤時刻期待値
        $expectedDate = $now->copy()->isoformat("MM/DD(ddd)");
        $searchDate = $now->copy()->format('Y-m-d');
        $attendanceData = Attendance::where('user_id', $user->id)
                            ->whereDate("work_end_datetime", $searchDate)
                            ->first();
        $expectedWorkEndTime = $attendanceData->work_end_datetime->format("H:i");

        $response->assertSee($expectedDate);
        $response->assertSee($expectedWorkEndTime);
    }

    //9 勤怠一覧情報取得機能
    public function test_attendance_勤怠一覧情報()
    {
        $user = User::find(36);
        $this->actingAs($user);
        $now = Carbon::now();
        $LastMonthDate = $now->copy()->subMonth();
        $NextMonthDate = $now->copy()->addMonth();
        $viewThisMonthDate = $now->copy()->format('Y/m');
        $viewLastMonthDate = $LastMonthDate->copy()->format('Y/m');
        $viewNextMonthDate = $NextMonthDate->copy()->format('Y/m');
        $searchDate = $now->copy()->format('Y-m-d');

        // 今月の出勤日数を算出
        $yesterday = $now->copy()->subDay();
        $workDays = 0;
        $startOfMonth = $now->copy()->startOfMonth();

        $currentDate = $startOfMonth->copy();
        while ($currentDate->lte($yesterday)) {
            // 現在の日付が平日 (月曜〜金曜) であるかチェック
            if ($currentDate->isWeekday()) {
                $workDays++;
            }

            $currentDate->addDay();
        }

        // 先月の出勤日数を算出
        $lastMonthWorkDays = 0;
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $lastMonthDate = $startOfLastMonth->copy();
        while ($lastMonthDate->lte($endOfLastMonth)) {
            // 日付が平日 (月曜〜金曜) であるかチェック
            if ($lastMonthDate->isWeekday()) {
                $lastMonthWorkDays++;
            }

            $lastMonthDate->addDay();
        }



        $attendanceThisMonthDatas = Attendance::where("user_id", $user->id)
                            ->whereYear("work_start_datetime", $now->year)
                            ->whereMonth("work_start_datetime", $now->month)->get();
        //今月の出勤日数が今月のAttendanceレコードの数と一致
        $this->assertEquals($workDays, $attendanceThisMonthDatas->count());

        $attendanceLastMonthDatas = Attendance::where("user_id", $user->id)
                            ->whereYear("work_start_datetime", $startOfLastMonth->year)
                            ->whereMonth("work_start_datetime", $startOfLastMonth->month)->get();
        //先月の出勤日数が先月のAttendanceレコードの数と一致
        $this->assertEquals($lastMonthWorkDays, $attendanceLastMonthDatas->count());

        //勤怠一覧画面
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($viewThisMonthDate);

        //先月の一覧画面
        $targetYear = $LastMonthDate->year;
        $targetMonth = $LastMonthDate->month;
        $response = $this->get(route('attendance.list', [
            'year' => $targetYear,
            'month' => $targetMonth,
        ]));
        $response->assertStatus(200);
        $response->assertSee($viewLastMonthDate);

        //翌月の一覧画面
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($viewThisMonthDate);
        $targetYear = $NextMonthDate->year;
        $targetMonth = $NextMonthDate->month;
        $response = $this->get(route('attendance.list', [
            'year' => $targetYear,
            'month' => $targetMonth,
        ]));
        $response->assertStatus(200);
        $response->assertSee($viewNextMonthDate);

        //詳細遷移確認
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $now,
            "work_end_datetime" => $now,
            "work_time" => 0,
        ]);

        $sampleAttendanceRecord = Attendance::where('user_id', $user->id)
                                    ->whereDate('work_start_datetime', $searchDate)->first();

        $sampleAttendanceRecordId = $sampleAttendanceRecord->id;
        $detailViewDate = $now->copy()->isoformat('M月D日');

        $response = $this->get(route('attendance.detail', [
            'id' => $sampleAttendanceRecordId
        ]));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($detailViewDate);

    }

    //10 勤怠詳細情報
    public function test_attendance_勤怠詳細画面()
    {
        $user = User::factory()->create([
            "name" => "テストネーム",
            "status" => 0
        ]);

        $now = Carbon::now();
        $detailViewDate = $now->copy()->isoformat("M月D日");
        $workStartDatetime = $now->copy()->setTime(8, 30, 0);
        $workEndDatetime = $now->copy()->setTime(17, 15, 0);
        $workTime = $workEndDatetime->diffInMinutes($workStartDatetime);

        $restStartDatetime = $now->copy()->setTime(12, 0, 0);
        $restEndDatetime = $now->copy()->setTime(13, 0, 0);
        $restTime = $restEndDatetime->diffInMinutes($restStartDatetime);

        $attendanceRecord = Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $workStartDatetime,
            "work_end_datetime" => $workEndDatetime,
            "work_time" => $workTime,
        ]);
        $attendanceRecordId = $attendanceRecord->id;

        $restRecord = Rest::create([
            "user_id" => $user->id,
            "attendance_id" => $attendanceRecord->id,
            "rest_start_datetime" => $restStartDatetime,
            "rest_end_datetime" => $restEndDatetime,
            "rest_time" => $restTime,
        ]);

        $this->actingAs($user);

        //勤怠詳細画面表示
        $response = $this->get(route('attendance.detail', [
            'id' => $attendanceRecordId
        ]));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user["name"]);
        $response->assertSee($detailViewDate);
        $response->assertSee($workStartDatetime->format("H:i"));
        $response->assertSee($workEndDatetime->format("H:i"));
        $response->assertSee($restStartDatetime->format("H:i"));
        $response->assertSee($restEndDatetime->format("H:i"));
    }

    //11 勤怠詳細情報修正機能
    public function test_attendance_勤怠詳細修正()
    {
        $user = User::factory()->create([
            "name" => "テストネーム",
            "status" => 0
        ]);

        $now = Carbon::now();
        $detailViewDate = $now->copy()->isoformat("M月D日");
        $workStartDatetime = $now->copy()->setTime(8, 30, 0);
        $workEndDatetime = $now->copy()->setTime(17, 15, 0);
        $workTime = $workEndDatetime->diffInMinutes($workStartDatetime);

        $restStartDatetime = $now->copy()->setTime(12, 0, 0);
        $restEndDatetime = $now->copy()->setTime(13, 0, 0);
        $restTime = $restEndDatetime->diffInMinutes($restStartDatetime);

        $attendanceRecord = Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $workStartDatetime,
            "work_end_datetime" => $workEndDatetime,
            "work_time" => $workTime,
        ]);
        $attendanceRecordId = $attendanceRecord->id;

        $restRecord = Rest::create([
            "user_id" => $user->id,
            "attendance_id" => $attendanceRecord->id,
            "rest_start_datetime" => $restStartDatetime,
            "rest_end_datetime" => $restEndDatetime,
            "rest_time" => $restTime,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.detail', [
            'id' => $attendanceRecordId
        ]));
        $response->assertStatus(200);

        //出勤時間が退勤時間よりも後になっている場合のエラー
        $correctionData = [
            "work_start" => "18:00",
            "work_end" => "17:15",
            "rest_start" => ["12:00"],
            "rest_end" => ["13:00"],
            "remarks" => "時間変更",

            "attendance_id" => $attendanceRecord->id,
            "work_start_date" => $attendanceRecord->work_start_datetime->format("Y-m-d"),
            "work_end_date" => $attendanceRecord->work_end_datetime->format("Y-m-d"),
            "rest_id" => [$restRecord->id],
            "rest_start_date" => [$restRecord->rest_start_datetime->format("Y-m-d")],
            "rest_end_date" => [$restRecord->rest_end_datetime->format("Y-m-d")],
        ];

        $response = $this->post("/stamp_correction_request", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecordId]));
        $response->assertSessionHasErrors(['work_end']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecordId]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("出勤時間もしくは退勤時間が不適切な値です");

        //休憩開始時間が退勤時間よりも後になっている場合のエラー
        $correctionData = [
            "work_start" => "9:00",
            "work_end" => "17:15",
            "rest_start" => ["18:00"],
            "rest_end" => ["19:00"],
            "remarks" => "時間変更",

            "attendance_id" => $attendanceRecord->id,
            "work_start_date" => $attendanceRecord->work_start_datetime->format("Y-m-d"),
            "work_end_date" => $attendanceRecord->work_end_datetime->format("Y-m-d"),
            "rest_id" => [$restRecord->id],
            "rest_start_date" => [$restRecord->rest_start_datetime->format("Y-m-d")],
            "rest_end_date" => [$restRecord->rest_end_datetime->format("Y-m-d")],
        ];

        $response = $this->post("/stamp_correction_request", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecordId]));
        $response->assertSessionHasErrors(['rest_start.0']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecordId]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("休憩時間が勤務時間外です");

        //休憩終了時間が退勤時間よりも後になっている場合のエラー
        $correctionData = [
            "work_start" => "9:00",
            "work_end" => "17:15",
            "rest_start" => ["16:00"],
            "rest_end" => ["19:00"],
            "remarks" => "時間変更",

            "attendance_id" => $attendanceRecord->id,
            "work_start_date" => $attendanceRecord->work_start_datetime->format("Y-m-d"),
            "work_end_date" => $attendanceRecord->work_end_datetime->format("Y-m-d"),
            "rest_id" => [$restRecord->id],
            "rest_start_date" => [$restRecord->rest_start_datetime->format("Y-m-d")],
            "rest_end_date" => [$restRecord->rest_end_datetime->format("Y-m-d")],
        ];

        $response = $this->post("/stamp_correction_request", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecordId]));
        $response->assertSessionHasErrors(['rest_end.0']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecordId]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("休憩時間が勤務時間外です");

        //備考欄未入力時のエラー
        $correctionData = [
            "work_start" => "9:00",
            "work_end" => "17:15",
            "rest_start" => ["13:00"],
            "rest_end" => ["14:00"],
            "remarks" => "",

            "attendance_id" => $attendanceRecord->id,
            "work_start_date" => $attendanceRecord->work_start_datetime->format("Y-m-d"),
            "work_end_date" => $attendanceRecord->work_end_datetime->format("Y-m-d"),
            "rest_id" => [$restRecord->id],
            "rest_start_date" => [$restRecord->rest_start_datetime->format("Y-m-d")],
            "rest_end_date" => [$restRecord->rest_end_datetime->format("Y-m-d")],
        ];

        $response = $this->post("/stamp_correction_request", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecordId]));
        $response->assertSessionHasErrors(['remarks']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecordId]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("備考を記入してください");

        //正しく処理された場合
        $correctionData = [
            "work_start" => "09:00",
            "work_end" => "18:00",
            "rest_start" => ["13:00"],
            "rest_end" => ["14:00"],
            "remarks" => "時間変更",

            "attendance_id" => $attendanceRecord->id,
            "work_start_date" => $attendanceRecord->work_start_datetime->format("Y-m-d"),
            "work_end_date" => $attendanceRecord->work_end_datetime->format("Y-m-d"),
            "rest_id" => [$restRecord->id],
            "rest_start_date" => [$restRecord->rest_start_datetime->format("Y-m-d")],
            "rest_end_date" => [$restRecord->rest_end_datetime->format("Y-m-d")],
        ];

        $response = $this->post("/stamp_correction_request", $correctionData);
        $response->assertStatus(302);

        $response = $this->get(route('attendance.detail', ['id' => $attendanceRecordId]));
        $response->assertStatus(200);

        $response->assertSee("承認待ちのため修正はできません");

        //申請したレコードがすべて表示されているかを確認
        $attendanceRequestRecords = UserRequest::where("user_id", $user->id)
                                    ->where("approve_status", 0)
                                    ->get();
        $response = $this->get("/stamp_correction_request/list");
        $response->assertStatus(200);
        $htmlContent = $response->content();
        $count = substr_count($htmlContent, $user->name);
        $this->assertEquals($attendanceRequestRecords->count(), $count);

        //詳細を押すと申請詳細に遷移
        $response->assertSee('<a class="link-detail" href="/attendance/' . $attendanceRecordId . '">詳細</a>', false);
        $response = $this->get('/attendance/'.$attendanceRecordId);
        $response->assertStatus(200);
        $response->assertSee($attendanceRecord->work_start_datetime->isoformat("M月D日"));
        $response->assertSee("承認待ちのため修正はできません");

        //管理者で承認
        $adminUser = Admin::create([
            'name' => '管理者',
            'email' => 'testadmin@example.com',
            'password' => \Hash::make('password'),
        ]);
        $this->actingAs($adminUser, 'admin');
        $requestRecord = UserRequest::where("user_id", $user->id)
                        ->where("approve_status", 0)
                        ->whereDate("work_start_datetime", $now->format("Y-m-d"))->first();
        $response = $this->get(route('correction.approve', ['attendance_correct_request' => $requestRecord->id ]));
        $response->assertStatus(200);
        $response->assertSee("承認");
        $response = $this->post('/request/approve',[
            "requestId" => $requestRecord->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('correction.approve', ['attendance_correct_request' => $requestRecord->id ]));

        $followedresponse = $this->get(route('correction.approve', ['attendance_correct_request' => $requestRecord->id ]));
        $followedresponse->assertStatus(200);
        $followedresponse->assertSee("承認済み");

        //「承認済み」に管理者が承認した修正申請が全て表示されている

        $attendanceApproveRecords = UserRequest::where("user_id", $user->id)
                                    ->where("approve_status", 1)
                                    ->get();

        $this->actingAs($user);
        $response = $this->get("stamp_correction_request/list?tab=approve");
        $response->assertStatus(200);

        $htmlContent = $response->content();
        $count = substr_count($htmlContent, $user->name);
        $this->assertEquals($attendanceRequestRecords->count(), $count);






    }

}
