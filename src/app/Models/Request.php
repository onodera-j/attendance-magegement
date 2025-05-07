<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'attendance_in',
        'attendance_out',
        'rest_id_1',
        'rest_in_1',
        'rest_out_1',
        'rest_id_2',
        'rest_in_2',
        'rest_out_2',
        'remarks',
        'application',
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

    public function approve()
    {
        return $this->hasOne(Approve::class);
    }
}
