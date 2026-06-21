<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicRule extends Model
{
    protected $fillable = [
        'endpoint_id', 'condition_type', 'condition_key', 'condition_operator',
        'condition_value', 'response_status', 'response_body'
    ];

    protected $casts = [
        'response_body' => 'array',
        'created_at' => 'date:Y-m-d\TH:i:s',
        'updated_at' => 'date:Y-m-d\TH:i:s',
    ];

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class);
    }
}
