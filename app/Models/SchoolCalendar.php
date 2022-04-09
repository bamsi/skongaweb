<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{

    protected $fillable = [
        'id',
        'name',
        'code',
        'start_date',
        'end_date',
        'institution_id',
        'level_id',
        'active'
    ];

    protected $table = 'school_calendar';
    protected $primaryKey = 'id';
}
