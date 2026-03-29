<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = ['name', 'fee', 'requirements', 'custom_fields', 'icon', 'color', 'payment_methods'];

    protected $casts = [
        'requirements' => 'array',
        'custom_fields' => 'array',
        'payment_methods' => 'array',
    ];
}
