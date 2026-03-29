<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_event_id',
        'title',
        'type',
        'start_datetime',
        'end_datetime',
        'status',
        'description',
        'priest_id',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function priest()
    {
        return $this->belongsTo(User::class, 'priest_id');
    }
}
