<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
    'name', 'email', 'mobile_number', 'position', 'sex',
    'marital_status', 'age', 'address', 'profile_image'
];

}
