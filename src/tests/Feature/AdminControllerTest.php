<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserAttendanceSeeder;
use Database\Seeders\AdminSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request as UserRequest;
use App\Models\RestRequest;
use Carbon\Carbon;

class AdminControllerTest extends TestCase
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
        $this->seed(AdminSeeder::class);

        Carbon::setTestNow(Carbon::now());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    //12 勤怠一覧情報取得
    public function test_admin_勤怠一覧情報()
    {
        $user = User::factory()->create(["name"=>"テストユーザー"]);
        $adminUser = Admin::find(1);
        $now = Carbon::now();
        $dayBefore = $now->copy()->subDay();
        $nextDay = $now->copy()->addDay();
        $workStart = $now->copy()->setTime(12,0);
        $workEnd = $workStart->copy()->addHour();
        $displayDate = $now->copy()->isoformat("Y年M月D日");
        $displayWorkStart = $workStart->copy()->format("H:i");
        $displayWorkEnd = $workEnd->copy()->format("H:i");

        Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $workStart,
            "work_end_datetime" => $workEnd,
            "work_time" => 60,
        ]);

        $this->actingAs($adminUser, 'admin');
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($displayDate."の勤怠");
        $response->assertSee("$user->name");
        $response->assertSee("$displayWorkStart");
        $response->assertSee("$displayWorkEnd");
        $response->assertSee("1:00");

        //前日の勤怠情報一覧
        $targetYear = $dayBefore->year;
        $targetMonth = $dayBefore->month;
        $targetDay = $dayBefore->day;
        $displayDayBefore = $dayBefore->copy()->isoformat("Y年M月D日");

        $response = $this->get(route('admin.attendance.list', [
            'year' => $targetYear,
            'month' => $targetMonth,
            'day' => $targetDay,
        ]));
        $response->assertStatus(200);
        $response->assertSee($displayDayBefore);

        //翌日の勤怠情報一覧
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $targetYear = $nextDay->year;
        $targetMonth = $nextDay->month;
        $targetDay = $nextDay->day;
        $displayNextDay = $nextDay->copy()->isoformat("Y年M月D日");

        $response = $this->get(route('admin.attendance.list', [
            'year' => $targetYear,
            'month' => $targetMonth,
            'day' => $targetDay,
        ]));
        $response->assertStatus(200);
        $response->assertSee($displayNextDay);
    }

    //13 勤怠詳細情報取得・修正
    public function test_admin_勤怠詳細情報・修正()
    {
        $user = User::factory()->create(["name"=>"テストユーザー"]);
        $adminUser = Admin::find(2);
        $now = Carbon::now();
        $dayBefore = $now->copy()->subDay();
        $nextDay = $now->copy()->addDay();
        $workStart = $now->copy()->setTime(12,0);
        $workEnd = $workStart->copy()->addHour();
        $restStart = $now->copy()->setTime(12,30);
        $restEnd = $restStart->copy()->addMinutes(15);
        $displayDate = $now->copy()->isoformat("Y年M月D日");
        $displayWorkStart = $workStart->copy()->format("H:i");
        $displayWorkEnd = $workEnd->copy()->format("H:i");
        $displayRestStart = $restStart->copy()->format("H:i");
        $displayRestEnd = $restEnd->copy()->format("H:i");

        $attendanceRecord = Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $workStart,
            "work_end_datetime" => $workEnd,
            "work_time" => 60,
        ]);

        $restRecord = Rest::create([
            "user_id" => $user->id,
            "attendance_id" => $attendanceRecord->id,
            "rest_start_datetime" => $restStart,
            "rest_end_datetime" => $restEnd,
            "rest_time" => 15,
            "rest_status" => 0,
        ]);


        $this->actingAs($adminUser, 'admin');
        $response = $this->get(route('attendance.detail', [
            'id' => $attendanceRecord->id,
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

        $response = $this->post("/admin/attendance/correction", $correctionData);
        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecord->id]));
        $response->assertSessionHasErrors(['work_end']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecord->id]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("出勤時間もしくは退勤時間が不適切な値です");

        //休憩開始時間が退勤時間よりも後になっている場合のエラー
        $correctionData = [
            "work_start" => "09:00",
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

        $response = $this->post("/admin/attendance/correction", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecord->id]));
        $response->assertSessionHasErrors(['rest_start.0']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecord->id]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("休憩時間が勤務時間外です");

        //休憩終了時間が退勤時間よりも後になっている場合のエラー
        $correctionData = [
            "work_start" => "09:00",
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

        $response = $this->post("/admin/attendance/correction", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecord->id]));
        $response->assertSessionHasErrors(['rest_end.0']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecord->id]));

        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("休憩時間が勤務時間外です");

        //備考欄未入力時のエラー
        $correctionData = [
            "work_start" => "09:00",
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

        $response = $this->post("/admin/attendance/correction", $correctionData);

        $response->assertStatus(302);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendanceRecord->id]));
        $response->assertSessionHasErrors(['remarks']);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecord->id]));

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

        $response = $this->post("/admin/attendance/correction", $correctionData);
        $response->assertStatus(302);

        $followedResponse = $this->get(route('attendance.detail', ['id' => $attendanceRecord->id]));
        $followedResponse->assertStatus(200);
        $followedResponse->assertSee("09:00");
        $followedResponse->assertSee("18:00");
        $followedResponse->assertSee("13:00");
        $followedResponse->assertSee("14:00");
        $followedResponse->assertSee("時間変更");

    }

    //14 ユーザー情報取得
    public function test_admin_ユーザー情報取得(){
        $adminUser = Admin::find(3);
        $user = User::factory()->create(["name"=>"テストユーザー"]);

        $now = Carbon::now();
        $workStart = $now->copy()->setTime(12,0);
        $workEnd = $workStart->copy()->addHour();
        $restStart = $now->copy()->setTime(12,30);
        $restEnd = $restStart->copy()->addMinutes(15);
        $lastMonthDate = $now->copy()->subMonth();
        $nextMonthDate = $now->copy()->addMonth();
        $viewThisMonthDate = $now->copy()->format('Y/m');
        $viewLastMonthDate = $lastMonthDate->copy()->format('Y/m');
        $viewNextMonthDate = $nextMonthDate->copy()->format('Y/m');
        $viewDate = $now->copy()->isoformat('M月D日');

        $attendanceRecord = Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $workStart,
            "work_end_datetime" => $workEnd,
            "work_time" => 60,
        ]);
        $users = User::all();

        $this->actingAs($adminUser, 'admin');
        $response = $this->get(route('admin.staff.list'));
        $response->assertStatus(200);

        //すべてのユーザーが表示されているかを確認
        foreach($users as $user){
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }

        //ユーザーの勤怠情報を表示
        $response = $this->get(route('admin.staff.attendance.list', ['id' => $user->id]));
        $response->assertStatus(200);
        $response->assertSee("勤怠一覧");
        $response->assertSee($viewThisMonthDate);

        //ユーザーの前月の勤怠情報を表示
        $targetYear = $lastMonthDate->year;
        $targetMonth = $lastMonthDate->month;
        $url = route('admin.staff.attendance.list',['id' => $user->id]);
        $response = $this->get($url . '?year='.$targetYear . '&month='.$targetMonth,
        );
        $response->assertStatus(200);
        $response->assertSee($viewLastMonthDate);

        //ユーザーの翌月の勤怠情報を表示
        $response = $this->get(route('admin.staff.attendance.list', ['id' => $user->id]));
        $targetYear = $nextMonthDate->year;
        $targetMonth = $nextMonthDate->month;
        $url = route('admin.staff.attendance.list',['id' => $user->id]);
        $response = $this->get($url . '?year='.$targetYear . '&month='.$targetMonth,
        );
        $response->assertStatus(200);
        $response->assertSee($viewNextMonthDate);

        //詳細に遷移
        $response = $this->get(route('admin.staff.attendance.list', ['id' => $user->id]));
        $response->assertStatus(200);
        $response->assertSee("詳細");
        $response = $this->get(route('attendance.detail', ['id' => $attendanceRecord->id]));
        $response->assertStatus(200);
        $response->assertSee("勤怠詳細");
        $response->assertSee($user->name);
        $response->assertSee($viewDate);
    }

    //15 勤怠情報修正機能
    public function test_admin_勤怠修正承認(){
        $adminUser = Admin::find(4);
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

        $requestWorkStartDatetime = $now->copy()->setTime(9, 00, 0);
        $requestWorkEndDatetime = $now->copy()->setTime(18, 00, 0);
        $requestWorkTime = $requestWorkEndDatetime->diffInMinutes($requestWorkStartDatetime);

        $requestRestStartDatetime = $now->copy()->setTime(12, 30, 0);
        $requestRestEndDatetime = $now->copy()->setTime(14, 0, 0);
        $requestRestTime = $requestRestEndDatetime->diffInMinutes($requestRestStartDatetime);

        $attendanceRecord = Attendance::create([
            "user_id" => $user->id,
            "work_start_datetime" => $workStartDatetime,
            "work_end_datetime" => $workEndDatetime,
            "work_time" => $workTime,
            "pending" => 1,
        ]);

        $restRecord = Rest::create([
            "user_id" => $user->id,
            "attendance_id" => $attendanceRecord->id,
            "rest_start_datetime" => $restStartDatetime,
            "rest_end_datetime" => $restEndDatetime,
            "rest_time" => $restTime,
            "rest_status" => 0,
        ]);

        $requestRecord = UserRequest::create([
            "user_id" => $user->id,
            "attendance_id" => $attendanceRecord->id,
            "work_start_datetime" => $requestWorkStartDatetime,
            "work_end_datetime" => $requestWorkEndDatetime,
            "remarks" => "打刻修正",
            "requested_at" => $now,
        ]);

        $restRequestRecord = RestRequest::create([
            "request_id" => $requestRecord->id,
            "rest_id" => $restRecord->id,
            "rest_start_datetime" => $requestRestStartDatetime,
            "rest_end_datetime" => $requestRestEndDatetime,
        ]);

        $requestRecords = UserRequest::where("approve_status", 0)
                                ->get();

        //承認待ちリストの表示
        $this->actingAs($adminUser, 'admin');
        $response = $this->get(route('stamp_correction_request.list'));
        $response->assertStatus(200);
        $response->assertSee('申請一覧');
        $htmlContent = $response->content();
        $count = substr_count($htmlContent, "詳細") - 1; //見出し分
        $this->assertEquals($requestRecords->count(), $count);

        //申請詳細確認
        $response = $this->get(route('correction.approve',["attendance_correct_request" => $requestRecord->id]));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user->name);
        $response->assertSee("09:00");
        $response->assertSee("18:00");
        $response->assertSee("12:30");
        $response->assertSee("14:00");
        $response->assertSee("打刻修正");
        $response->assertSee('承認');

        //承認処理
        $response = $this->post('request/approve',[
            "requestId" => $requestRecord->id,
            "requestRestId" => [$restRequestRecord->id],
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('correction.approve', ['attendance_correct_request' => $requestRecord->id ]));
        $followedresponse = $this->get(route('correction.approve', ['attendance_correct_request' => $requestRecord->id ]));
        $followedresponse->assertStatus(200);
        $followedresponse->assertSee("承認済み");

        //承認済みリストの表示
        $approveRecords = UserRequest::where("approve_status", 1)
                                ->get();
        $response = $this->get(route('stamp_correction_request.list', [
            "tab" => "approve"
        ]));
        $response->assertStatus(200);
        $response->assertSee('申請一覧');
        $htmlContent = $response->content();
        $count = substr_count($htmlContent, "詳細") - 1; //見出し分
        $this->assertEquals($approveRecords->count(), $count);
    }

    

}
