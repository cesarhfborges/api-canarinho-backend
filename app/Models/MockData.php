<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockData extends Model
{
    protected $table = 'mock_data';

    protected $fillable = [
        'endpoint_id', 'json_data'
    ];

    protected $casts = [
        'json_data' => 'array',
        'created_at' => 'date:Y-m-d\TH:i:s',
        'updated_at' => 'date:Y-m-d\TH:i:s',
    ];

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class);
    }
}
