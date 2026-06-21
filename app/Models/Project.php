<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug'
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d\TH:i:s',
        'updated_at' => 'date:Y-m-d\TH:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function endpoints()
    {
        return $this->hasMany(Endpoint::class);
    }

    public function tokens()
    {
        return $this->hasMany(ProjectToken::class);
    }
}
