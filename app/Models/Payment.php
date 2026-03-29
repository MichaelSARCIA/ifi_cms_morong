<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added this line
use App\Models\ServiceRequest; // Added this line
use App\Models\User; // Added this line

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'amount',
        'amount_tendered',
        'payment_method',
        'reference_number',
        'receipt_number',
        'paid_at',
        'processed_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Generate unique receipt number
    public static function generateReceiptNumber()
    {
        $year = date('Y');
        $lastReceipt = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastReceipt ? ((int) substr($lastReceipt->receipt_number, -4)) + 1 : 1;

        return 'REC-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
