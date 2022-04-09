<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Institution extends Model
{
    //use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'address',
        'telephone1',
        'telephone2',
        'fax',
        'email',
        'website',
        'is_active'
    ];

    protected $hidden = ['pivot'];

    protected $table = 'institution';
    protected $primaryKey = 'id';

    public function user()
    {
      $this->hasOne(user::class);
    }

}
