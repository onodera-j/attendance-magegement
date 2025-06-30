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
        'requested_at',
        'approve_status',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approve()
    {
        return $this->hasOne(Approve::class);
    }

    public function restRequest()
    {
        return $this->hasMany(RestRequest::class);
    }
}
