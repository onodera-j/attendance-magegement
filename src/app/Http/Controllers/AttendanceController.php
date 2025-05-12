<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function attendance()
    {
        $user = Auth::user();
        $today = Carbon::now();
        $date = $today->isoformat('Y年M月D日(ddd)');
        $time = $today->format('H：i');

        return view('attendance.attendance', compact('date','time','user'));
    }

    public function list()
    {
        return view('attendance.list');
    }

}
