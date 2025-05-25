<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approve extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requesr_id',
        'remarks',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    public function restApprove()
    {
        return $this->hasMany(RestApprove::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }


}
