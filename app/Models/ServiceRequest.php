<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'service_type',
        'scheduled_date',
        'scheduled_time',
        'status',
        'payment_status',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'fathers_name',
        'mothers_name',
        'contact_number',
        'email',
        'priest_id',
        'details',
        'requirements',
        'custom_data'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'requirements' => 'array',
        'custom_data' => 'array',
    ];

    protected $appends = ['applicant_name'];

    public function priest()
    {
        return $this->belongsTo(User::class, 'priest_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    public function getApplicantNameAttribute()
    {
        $fn = trim($this->first_name ?? '');
        $mn = trim($this->middle_name ?? '');
        $ln = trim($this->last_name ?? '');
        $sf = trim($this->suffix   ?? '');

        // Standard name construction
        $parts = [];
        if (!empty($fn)) $parts[] = $fn;
        
        // Handle Middle Name: Skip if N/A to keep the displayed name clean
        if (!empty($mn) && strtoupper($mn) !== 'N/A') {
            $parts[] = $mn;
        }
        
        if (!empty($ln)) $parts[] = $ln;
        
        // Handle Suffix: Skip if N/A
        if (!empty($sf) && strtoupper($sf) !== 'N/A') {
            $parts[] = $sf;
        }

        $name = implode(' ', $parts);

        if (!empty($name)) return $name;

        // Fallback to custom_data names if available
        $data = $this->custom_data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (is_array($data)) {
            if (!empty($data['deceaseds_full_name'])) return $data['deceaseds_full_name'];
            if (!empty($data['full_name'])) return $data['full_name'];
            if (!empty($data['name'])) return $data['name'];
        }

        return 'Guest / Applicant';
    }

    /**
     * Helper to format values for reports/certificates.
     * Replaces "N/A" (case-insensitive) with an empty string.
     */
    public static function formatValue($value)
    {
        if (is_null($value)) return '';
        if (strtoupper(trim((string)$value)) === 'N/A') return '';
        return $value;
    }

    /**
     * Returns custom_data with "N/A" values filtered out.
     */
    public function getFilteredCustomDataAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }
        if (!is_array($data)) return [];

        return array_map(function($val) {
            return self::formatValue($val);
        }, $data);
    }
}
