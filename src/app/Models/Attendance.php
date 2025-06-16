<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_start_datetime',
        'work_end_datetime',
        'work_time',
        'total_time',
        'pending',
        'remarks'
    ];

    protected $casts = [
        'work_start_datetime' => 'datetime',
        'work_end_datetime' => 'datetime',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function rest()
    {
        return $this->hasMany(Rest::class);
    }

    public function request()
    {
        return $this->hasMany(Request::class);
    }

    public function approve()
    {
        return $this->hasMany(Approve::class);
    }

}
