<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sacrament extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date_performed',
        'priest_name',
        'details',
        'remarks'
    ];
}
