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
        'work_start_datetime',
        'work_end_datetime',
        'remarks',
        'requested_at'
    ];

    protected $casts = [
        'work_start_datetime' => 'datetime',
        'work_end_datetime' => 'datetime',
        'requested_at' => 'datetime'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    public function restRequest()
    {
        return $this->hasMany(RestRequest::class);
    }

    public function approve()
    {
        return $this->hasOne(Approve::class);
    }
}
