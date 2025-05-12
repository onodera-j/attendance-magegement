<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function attendanceList()
    {
        $today = Carbon::now();
        $date = $today->isoformat('Y年M月D日(ddd)');
        $time = $today->format('H：i');

        return view('admin.attendance_list', compact('date','time'));
    }

}
