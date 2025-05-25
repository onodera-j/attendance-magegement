<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'rest_id',
        'rest_start_datetime',
        'rest_end_datetime',
    ];

    protected $casts = [
        'rest_start_datetime' => 'datetime', // ここを追加
        'rest_end_datetime' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id', 'id');
    }

    public function rest()
    {
        return $this->belongsTo(Rest::class, 'rest_id', 'id');
    }

    public function restApprove()
    {
        return $this->hasOne(RestApprove::class);
    }
}
