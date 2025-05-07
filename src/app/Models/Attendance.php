<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'pending'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,user_id,id);
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
