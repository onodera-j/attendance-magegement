<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestApprove extends Model
{
    use HasFactory;

    protected $fillable = [
        'approve_id',
        'rest_id',
        'rest_request_id',
    ];

    public function approve()
    {
        return $this->belongsTo(Approve::class, 'approve_id', 'id');
    }

    public function rest()
    {
        return $this->belongsTo(Rest::class, 'rest_id', 'id');
    }

}
