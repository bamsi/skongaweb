<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    //use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'code',
        'level_id',
        'institution_id',
        'is_active'
    ];

    protected $table = 'class';
    protected $primaryKey = 'id';
    
}
