<?php

namespace App\Models\Opd;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\LabRequest;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\CsrRequest;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Yakap\YakapEmpanelment;
use App\Models\Yakap\YakapFpeRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class OpdConsultation extends Model
{
    use ScopedByTenant;
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'is_yakap' => 'boolean',
        'fpe_completed' => 'boolean',
        'consultation_datetime' => 'datetime',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    // Master Data Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function opdClinic()
    {
        return $this->belongsTo(OpdClinic::class);
    }

    public function attendingPhysician()
    {
        return $this->belongsTo(HealthCareProfessional::class, 'attending_physician_id');
    }

    public function consultationType()
    {
        return $this->belongsTo(OpdConsultationType::class);
    }

    public function visitType()
    {
        return $this->belongsTo(OpdVisitType::class);
    }

    public function referralSource()
    {
        return $this->belongsTo(OpdReferralSource::class);
    }

    // YAKAP Relationships
    public function empanelment()
    {
        return $this->belongsTo(YakapEmpanelment::class, 'empanelment_id');
    }

    public function yakapFpeRecord()
    {
        return $this->hasOne(YakapFpeRecord::class, 'consultation_id');
    }

    // Requests Relationships
    public function labRequests()
    {
        return $this->hasMany(LabRequest::class);
    }

    public function pharmacyRequests()
    {
        return $this->hasMany(PharmacyRequest::class);
    }

    public function csrRequests()
    {
        return $this->hasMany(CsrRequest::class);
    }

    public function radiologyRequests()
    {
        return $this->hasMany(RadiologyRequest::class);
    }

    public function procedureRequests()
    {
        return $this->hasMany(ProcedureRequest::class);
    }
}
