<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function attendance()
    {
        $today = Carbon::now();
        $date = $today->isoformat('Y年M月D日(ddd)');
        $time = $today->format('H：i');

        return view('attendance.attendance', compact('date','time'));
    }

    public function list()
    {
        return view('attendance.list');
    }

}
