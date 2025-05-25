<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'rest_start_datetime',
        'rest_end_datetime',
        'rest_time',
    ];

    protected $casts = [
        'rest_start_datetime' => 'datetime',
        'rest_end_datetime' => 'datetime',
    ];

    public $timestamps = false;

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    public function restRequest()
    {
        return $this->hasMany(RestRequest::class);
    }

    public function restApprove()
    {
        return $this->hasMany(RestApprove::class);
    }


}
