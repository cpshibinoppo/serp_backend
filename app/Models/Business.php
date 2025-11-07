<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'tax_number',
        'business_code',
    ];
}
