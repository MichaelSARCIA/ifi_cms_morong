<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceRequest;
use App\Models\User;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'certificate_number',
        'issued_at',
        'issued_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    // Relationships
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // Generate unique certificate number
    public static function generateCertificateNumber()
    {
        $year = date('Y');
        $lastCert = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastCert ? ((int) substr($lastCert->certificate_number, -4)) + 1 : 1;

        return 'CERT-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
