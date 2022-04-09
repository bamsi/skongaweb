<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //use HasFactory;
    protected $fillable = [
        'preferred_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'country',
        'date_of_registration',
        'religion',
        'tribe',
        'disability',
        'institution_id',
        'active',
        'comments',
        'address',
        'phone',
        'email'
    ];

    protected $table = 'student';
    protected $primaryKey = 'id';
}
