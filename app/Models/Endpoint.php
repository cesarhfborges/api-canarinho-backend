<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    protected $fillable = [
        'project_id', 'name', 'generator', 'endpoints_config', 'resource_schema'
    ];

    protected $casts = [
        'endpoints_config' => 'array',
        'resource_schema' => 'array',
        'created_at' => 'date:Y-m-d\TH:i:s',
        'updated_at' => 'date:Y-m-d\TH:i:s',
    ];

    protected $hidden = ['endpoints_config', 'resource_schema'];
    protected $appends = ['endpoints', 'resourceSchema'];

    public function getEndpointsAttribute()
    {
        return json_decode($this->attributes['endpoints_config'] ?? '[]', true);
    }

    public function getResourceSchemaAttribute()
    {
        return json_decode($this->attributes['resource_schema'] ?? '[]', true);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function rules()
    {
        return $this->hasMany(DynamicRule::class);
    }

    public function mockData()
    {
        return $this->hasMany(MockData::class);
    }
}
