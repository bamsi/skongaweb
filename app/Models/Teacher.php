<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Subject;

class Teacher extends Model
{
    //use HasFactory;

    
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'address',
        'phone',
        'email',
        'gender',
        'institution_id',
        'class_teacher'
    ];

    protected $table = 'teacher';
    protected $primaryKey = 'id';

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')->withPivot('teacher_id');
    }

}
