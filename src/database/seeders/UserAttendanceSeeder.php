<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class UserAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory()->count(6)->create();

        foreach($users as $user) {
            $this->command->info("Seeding attendance for user: {$user->name} (ID: {$user->id})");

            $today = Carbon::today();

            $startOfLastMonth = $today->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();
            $this->seedMonthlyAttendance($user, $startOfLastMonth, $endOfLastMonth);

            $startOfCurrentMonth = $today->copy()->startOfMonth();
            $yesterday = $today->copy()->subDay();

            if($startOfCurrentMonth->lte($yesterday)){
                $this->seedMonthlyAttendance($user, $startOfCurrentMonth, $yesterday);
            }else {
                $this->command->warn("No attendance to seed for current month (until yesterday) for user: {$user->name}. Today is {$today->toDateString()}.");
        }
    }
}
        protected function seedMonthlyAttendance(User $user, Carbon $startDate, Carbon $endDate)
        {
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // 月曜から金曜日のみを対象
            if ($currentDate->isWeekday()) { // isWeekday()は月～金がtrue
                $this->command->line("  Processing {$currentDate->toDateString()} for {$user->name}...");

                // 出勤時間と退勤時間を設定
                $workStartTime = $currentDate->copy()->setTime(9, 0, 0); // 9:00 出勤
                $workEndTime = $currentDate->copy()->setTime(18, 0, 0);   // 18:00 退勤
                $totalWorkDurationMinutes = $workEndTime->diffInMinutes($workStartTime); // 総勤務時間 (休憩含まず)

                // 勤怠データを作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_start_datetime' => $workStartTime,
                    'work_end_datetime' => $workEndTime,
                    'work_time' => $totalWorkDurationMinutes, // 実労働時間計算前の時間
                ]);

                // 休憩データを作成 (例: 1時間の休憩)
                $restStartTime = $currentDate->copy()->setTime(12, 0, 0); // 12:00 休憩開始
                $restEndTime = $currentDate->copy()->setTime(13, 0, 0);   // 13:00 休憩終了
                $restDurationMinutes = $restEndTime->diffInMinutes($restStartTime); // 休憩時間

                Rest::factory()->create([
                    'user_id' => $user->id,
                    'attendance_id' => $attendance->id,
                    'rest_start_datetime' => $restStartTime,
                    'rest_end_datetime' => $restEndTime,
                    'rest_time' => $restDurationMinutes,
                    'rest_status' => 0, // 休憩終了
                ]);

            } else {
                $this->command->line("  Skipping weekend: {$currentDate->toDateString()}");
            }

            $currentDate->addDay(); // 次の日へ
        }
    }
}
