<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndpointCall extends Model
{
    protected $table = 'endpoint_calls';

    protected $fillable = [
        'endpoint_id',
        'method',
        'url',
        'status_code',
        'ip_address'
    ];

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class);
    }
}
