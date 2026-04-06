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
        'scheduled_date' => 'date:Y-m-d',
        'requirements' => 'array',
        'custom_data' => 'array',
    ];

    protected $appends = ['applicant_name', 'subject_name'];

    public function priest()
    {
        return $this->belongsTo(User::class, 'priest_id');
    }

    public function getSubjectNameAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }
        
        $formatName = function($parts) {
            $cleaned = [];
            $hasNa = false;
            $firstNaValue = null;
            
            foreach ($parts as $p) {
                if (empty(trim($p))) continue;
                $pTrim = trim($p);
                $low = strtolower($pTrim);
                if (in_array($low, ['na', 'n/a', 'none', '-'])) {
                    $hasNa = true;
                    if (is_null($firstNaValue)) $firstNaValue = $pTrim;
                    continue;
                }
                $cleaned[] = $pTrim;
            }
            
            if (count($cleaned) > 0) return implode(' ', $cleaned);
            if ($hasNa) return $firstNaValue ?: 'N/A';
            return '';
        };
        
        $type = strtolower($this->service_type ?? '');
        
        // 1. Wedding: Groom & Bride
        if (str_contains($type, 'wedding')) {
            $groomFn = $data['grooms_details_first_name'] ?? $data['groom_details_first_name'] ?? '';
            $groomLn = $data['grooms_details_last_name'] ?? $data['groom_details_last_name'] ?? '';
            $brideFn = $data['brides_details_first_name'] ?? $data['bride_details_first_name'] ?? '';
            $brideLn = $data['brides_details_last_name'] ?? $data['bride_details_last_name'] ?? '';
            
            $groom = $formatName([$groomFn, $groomLn]);
            $bride = $formatName([$brideFn, $brideLn]);
            
            if ($groom && $bride) return "$groom & $bride";
            if ($groom || $bride) return $groom ?: $bride;
        }
        
        // 2. Baptism: Child's Name
        if (str_contains($type, 'baptism')) {
            $cfn = $data['childs_details_first_name'] ?? $data['child_details_first_name'] ?? $data['first_name'] ?? '';
            $cmn = $data['childs_details_middle_name'] ?? $data['child_details_middle_name'] ?? $data['middle_name'] ?? '';
            $cln = $data['childs_details_last_name'] ?? $data['child_details_last_name'] ?? $data['last_name'] ?? '';
            $sfx = $data['childs_details_suffix'] ?? $data['child_details_suffix'] ?? $data['suffix'] ?? '';
            $name = $formatName([$cfn, $cmn, $cln, $sfx]);
            if ($name) return $name;
        }
        
        // 3. Funeral/Wake: Deceased's Name
        if (str_contains($type, 'funeral') || str_contains($type, 'wake')) {
            $dfn = $data['deceaseds_information_first_name'] ?? $data['deceased_details_first_name'] ?? $data['first_name'] ?? '';
            $dmn = $data['deceaseds_information_middle_name'] ?? $data['deceased_details_middle_name'] ?? $data['middle_name'] ?? '';
            $dln = $data['deceaseds_information_last_name'] ?? $data['deceased_details_last_name'] ?? $data['last_name'] ?? '';
            $sfx = $data['deceaseds_information_suffix'] ?? $data['deceased_details_suffix'] ?? $data['suffix'] ?? '';
            $name = $formatName([$dfn, $dmn, $dln, $sfx]);
            if ($name) return $name;
        }
        
        // 4. Confirmation: Candidate's Name
        if (str_contains($type, 'confirmation')) {
            $cfn = $data['candidates_details_first_name'] ?? $data['candidate_details_first_name'] ?? $data['first_name'] ?? '';
            $cmn = $data['candidates_details_middle_name'] ?? $data['candidate_details_middle_name'] ?? $data['middle_name'] ?? '';
            $cln = $data['candidates_details_last_name'] ?? $data['candidate_details_last_name'] ?? $data['last_name'] ?? '';
            $sfx = $data['candidates_details_suffix'] ?? $data['candidate_details_suffix'] ?? $data['suffix'] ?? '';
            $name = $formatName([$cfn, $cmn, $cln, $sfx]);
            if ($name) return $name;
        }
        
        // Fallback to construction from root columns (Subject name columns)
        $fn = trim($this->first_name ?? '');
        $mn = trim($this->middle_name ?? '');
        $ln = trim($this->last_name ?? '');
        
        $name = $formatName([$fn, $mn, $ln]);
        if ($name) return $name;
        
        // Final fallback: use applicant_name if nothing else found
        $app = $this->applicant_name;
        $appClean = $formatName([$app]);
        return $appClean ?: '-';
    }

    public function getRecipientNameFormalAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) $data = json_decode($data, true) ?? [];
        if (!is_array($data)) $data = [];

        $formatFormal = function($fn, $mn, $ln) {
            $fn = trim($fn); $mn = trim($mn); $ln = trim($ln);
            $invalid = ['na', 'n/a', 'none', '-'];
            if (in_array(strtolower($fn), $invalid)) $fn = '';
            if (in_array(strtolower($mn), $invalid)) $mn = '';
            if (in_array(strtolower($ln), $invalid)) $ln = '';
            
            if (empty($fn) && empty($ln) && empty($mn)) return null;

            $mi = '';
            if (!empty($mn)) {
                $mi = strtoupper(substr($mn, 0, 1)) . '.';
            }
            
            $firstPart = $ln ? $ln . ', ' . $fn : $fn;
            return trim($firstPart . ' ' . $mi);
        };

        $type = strtolower($this->service_type ?? '');
        
        if (str_contains($type, 'wedding')) {
            $gFn = $data['grooms_details_first_name'] ?? $data['groom_details_first_name'] ?? '';
            $gMn = $data['grooms_details_middle_name'] ?? $data['groom_details_middle_name'] ?? '';
            $gLn = $data['grooms_details_last_name'] ?? $data['groom_details_last_name'] ?? '';
            
            $bFn = $data['brides_details_first_name'] ?? $data['bride_details_first_name'] ?? '';
            $bMn = $data['brides_details_middle_name'] ?? $data['bride_details_middle_name'] ?? '';
            $bLn = $data['brides_details_last_name'] ?? $data['bride_details_last_name'] ?? '';
            
            $groom = $formatFormal($gFn, $gMn, $gLn);
            $bride = $formatFormal($bFn, $bMn, $bLn);
            
            if ($groom && $bride) return "$groom & $bride";
            if ($groom || $bride) return $groom ?: $bride;
        }

        if (str_contains($type, 'baptism')) {
            $fn = $data['childs_details_first_name'] ?? $data['first_name'] ?? '';
            $mn = $data['childs_details_middle_name'] ?? $data['middle_name'] ?? '';
            $ln = $data['childs_details_last_name'] ?? $data['last_name'] ?? '';
            $res = $formatFormal($fn, $mn, $ln);
            if ($res) return $res;
        }

        if (str_contains($type, 'funeral') || str_contains($type, 'wake')) {
            $fn = $data['deceaseds_information_first_name'] ?? $data['first_name'] ?? '';
            $mn = $data['deceaseds_information_middle_name'] ?? $data['middle_name'] ?? '';
            $ln = $data['deceaseds_information_last_name'] ?? $data['last_name'] ?? '';
            $res = $formatFormal($fn, $mn, $ln);
            if ($res) return $res;
        }

        if (str_contains($type, 'confirmation')) {
            $fn = $data['candidates_details_first_name'] ?? $data['first_name'] ?? '';
            $mn = $data['candidates_details_middle_name'] ?? $data['middle_name'] ?? '';
            $ln = $data['candidates_details_last_name'] ?? $data['last_name'] ?? '';
            $res = $formatFormal($fn, $mn, $ln);
            if ($res) return $res;
        }

        $fn = $this->first_name ?? '';
        $mn = $this->middle_name ?? '';
        $ln = $this->last_name ?? '';
        $res = $formatFormal($fn, $mn, $ln);
        if ($res) return $res;

        return 'N/A';
    }

    public function getRecipientDobAgeAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) $data = json_decode($data, true) ?? [];
        if (!is_array($data)) $data = [];

        $dob = '';
        $age = '';
        
        foreach ($data as $key => $val) {
            if (!is_string($val)) continue;
            $lKey = strtolower($key);
            if (empty(trim($val)) || in_array(strtolower(trim($val)), ['na', 'n/a', '-'])) continue;

            if (str_contains($lKey, 'date_of_birth') || str_contains($lKey, 'birthdate')) {
                if (empty($dob)) $dob = $val;
            }
            if (str_ends_with($lKey, '_age') || $lKey === 'age') {
                if (empty($age)) $age = $val;
            }
        }

        if (!empty($dob)) {
            try {
                $dob = \Carbon\Carbon::parse($dob)->format('M d, Y');
            } catch (\Exception $e) {}
        }

        if ($dob && $age) return "$dob ($age)";
        if ($dob) return $dob;
        if ($age) return "$age yrs old";
        return 'N/A';
    }

    public function getRecipientPobAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) $data = json_decode($data, true) ?? [];
        if (!is_array($data)) $data = [];

        foreach ($data as $key => $val) {
            if (!is_string($val)) continue;
            if (empty(trim($val)) || in_array(strtolower(trim($val)), ['na', 'n/a', '-'])) continue;
            if (str_contains(strtolower($key), 'place_of_birth')) {
                return $val;
            }
        }
        return 'N/A';
    }

    public function getRecipientParentsAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) $data = json_decode($data, true) ?? [];
        if (!is_array($data)) $data = [];

        $father = '';
        $mother = '';

        $formatFormal = function($fn, $mn, $ln) {
            $invalid = ['na', 'n/a', 'none', '-'];
            $fn = trim($fn); $mn = trim($mn); $ln = trim($ln);
            if (in_array(strtolower($fn), $invalid)) $fn = '';
            if (in_array(strtolower($mn), $invalid)) $mn = '';
            if (in_array(strtolower($ln), $invalid)) $ln = '';
            $mi = $mn ? strtoupper(substr($mn, 0, 1)) . '.' : '';
            $p = array_filter([$fn, $mi, $ln]);
            return count($p) > 0 ? implode(' ', $p) : '';
        };

        $prefix = ['grooms_details_', 'parents_information_', ''];
        
        foreach ($prefix as $pfx) {
            $f_fn = isset($data[$pfx.'fathers_first_name']) && is_string($data[$pfx.'fathers_first_name']) ? $data[$pfx.'fathers_first_name'] : '';
            $f_mn = isset($data[$pfx.'fathers_middle_name']) && is_string($data[$pfx.'fathers_middle_name']) ? $data[$pfx.'fathers_middle_name'] : '';
            $f_ln = isset($data[$pfx.'fathers_last_name']) && is_string($data[$pfx.'fathers_last_name']) ? $data[$pfx.'fathers_last_name'] : '';
            
            $m_fn = isset($data[$pfx.'mothers_first_name']) && is_string($data[$pfx.'mothers_first_name']) ? $data[$pfx.'mothers_first_name'] : '';
            $m_mn = isset($data[$pfx.'mothers_middle_name_maiden']) && is_string($data[$pfx.'mothers_middle_name_maiden']) ? $data[$pfx.'mothers_middle_name_maiden'] : (isset($data[$pfx.'mothers_middle_name']) && is_string($data[$pfx.'mothers_middle_name']) ? $data[$pfx.'mothers_middle_name'] : '');
            $m_ln = isset($data[$pfx.'mothers_last_name_maiden']) && is_string($data[$pfx.'mothers_last_name_maiden']) ? $data[$pfx.'mothers_last_name_maiden'] : (isset($data[$pfx.'mothers_last_name']) && is_string($data[$pfx.'mothers_last_name']) ? $data[$pfx.'mothers_last_name'] : '');
            
            $fName = $formatFormal($f_fn, $f_mn, $f_ln);
            $mName = $formatFormal($m_fn, $m_mn, $m_ln);
            
            if ($fName) $father = $fName;
            if ($mName) $mother = $mName;
            
            if ($father || $mother) break;
        }

        if ($father && $mother) return "F: $father\nM: $mother";
        if ($father) return "F: $father";
        if ($mother) return "M: $mother";
        return 'N/A';
    }

    public function getRecipientAddressAttribute()
    {
        $data = $this->custom_data;
        if (is_string($data)) $data = json_decode($data, true) ?? [];
        if (!is_array($data)) $data = [];

        foreach ($data as $key => $val) {
            if (!is_string($val)) continue;
            if (empty(trim($val)) || in_array(strtolower(trim($val)), ['na', 'n/a', '-'])) continue;
            if (str_contains(strtolower($key), 'complete_address') || strtolower($key) === 'address') {
                return $val;
            }
        }
        return 'N/A';
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
        // 1. TOP PRIORITY: Search custom_data for explicit "Applicant" or "Contact Person" fields
        $data = $this->custom_data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (is_array($data)) {
            foreach($data as $key => $val) {
                if (empty($val)) continue;
                $lowKey = strtolower($key);
                if ((str_contains($lowKey, 'applicant') && str_contains($lowKey, 'name')) || 
                    (str_contains($lowKey, 'contact_person') && str_contains($lowKey, 'name'))) {
                    return $val;
                }
            }
        }

        // 2. SECONDARY: Standard name construction from root columns (Subject name like Child or Deceased)
        $fn = trim($this->first_name ?? '');
        $mn = trim($this->middle_name ?? '');
        $ln = trim($this->last_name ?? '');
        $sf = trim($this->suffix   ?? '');

        // Fallback to custom_data for suffix if not found in root column
        if (empty($sf)) {
            if (is_array($data) && !empty($data['suffix'])) {
                $sf = trim($data['suffix']);
            }
        }

        // Standard name construction
        $parts = [];
        if (!empty($fn)) $parts[] = $fn;
        
        // Handle Middle Name: Skip if N/A to keep the displayed name clean
        if (!empty($mn) && strtoupper($mn) !== 'N/A') {
            $parts[] = $mn;
        }
        
        if (!empty($ln)) {
            // Check if the last name is already part of the first name to avoid redundancy (e.g., "Juan Reyes" + "Reyes")
            // This is a safety for cases where the full name was mirrored into first_name
            if (empty($fn) || stripos($fn, $ln) === false) {
                $parts[] = $ln;
            }
        }
        
        // Handle Suffix: Skip if N/A
        if (!empty($sf) && strtoupper($sf) !== 'N/A') {
            $parts[] = $sf;
        }

        $name = implode(' ', $parts);

        if (!empty($name)) return $name;

        // 3. FINAL FALLBACKS:
        if (is_array($data)) {

            // Check for common name suffixes if root columns were empty
            $fn = ''; $mn = ''; $ln = '';
            foreach ($data as $key => $value) {
                if (empty($value)) continue;
                $lowKey = strtolower($key);
                if ($lowKey === 'first_name' || str_ends_with($lowKey, '_first_name')) $fn = $value;
                if ($lowKey === 'last_name' || str_ends_with($lowKey, '_last_name')) $ln = $value;
                if ($lowKey === 'middle_name' || str_ends_with($lowKey, '_middle_name')) $mn = $value;
            }
            
            $full = trim($fn . ' ' . $ln);
            if ($full) return $full;

            // Secondary fallback for specific service keys
            if (!empty($data['full_name'])) return $data['full_name'];
            if (!empty($data['name'])) return $data['name'];
            
            // Final fallback to deceased (only if no applicant info found)
            if (!empty($data['deceaseds_full_name'])) return $data['deceaseds_full_name'];
            if (!empty($data['deceased_full_name'])) return $data['deceased_full_name'];
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
        if (is_array($value) || is_object($value)) return $value;
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
