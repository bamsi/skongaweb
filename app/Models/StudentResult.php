<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentResult extends Model
{
    //use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exam_schedule_id',
        'student_subject_id',
        'marks',
        'grade',
        'position'
    ];

    protected $table = 'student_result';
    protected $primaryKey = 'id';
}
