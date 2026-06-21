<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectToken extends Model
{
    protected $fillable = [
        'project_id', 'token', 'name'
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d\TH:i:s',
        'updated_at' => 'date:Y-m-d\TH:i:s',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
