<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approve extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_id_1',
        'rest_id_2',
        'request_id',
        'remarks',
        'requested_at',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class,attendance_id,id);
    }

    public function rest1()
    {
        return $this->belongsTo(Rest::class,rest_id_1,id);
    }

    public function rest2()
    {
        return $this->belongsTo(Rest::class,rest_id_2,id);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }


}
