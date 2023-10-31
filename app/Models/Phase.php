<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    use HasFactory;
    protected $appends = ['task_count'];

    protected $fillable = [
        'name',
    ];

    function tasks()
    {
        return $this->hasMany(Task::class);
    }

    function getTaskCountAttribute(){
        return $this->hasMany(Task::class)->count();
    }
}
