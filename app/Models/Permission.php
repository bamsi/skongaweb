<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Permission extends Model
{
    protected $fillable = [
        'id',
        'name'
    ];

    protected $hidden = ['pivot'];

    protected $table = 'permission';
    protected $primaryKey = 'id';


}
