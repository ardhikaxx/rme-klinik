<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientService
{
    /**
     * Search and paginate patients.
     */
    public function getPatients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Patient::query()->with(['allergies', 'emergencyContacts']);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('medical_record_number', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $query->where('is_verified', (bool) $filters['is_verified']);
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Check for potential duplicates.
     */
    public function findDuplicates(string $name, string $dob, ?string $nik = null, ?string $phone = null, ?int $excludeId = null): Collection
    {
        return Patient::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($name, $dob, $nik, $phone) {
                if ($nik) {
                    $q->where('nik', $nik);
                }
                $q->orWhere(function ($sub) use ($name, $dob) {
                    $sub->where('name', 'like', "%{$name}%")
                        ->where('date_of_birth', $dob);
                });
                if ($phone) {
                    $q->orWhere('phone', $phone);
                }
            })
            ->limit(5)
            ->get();
    }

    /**
     * Register a new patient.
     */
    public function createPatient(array $data, ?array $allergies = [], ?array $emergencyContacts = []): Patient
    {
        return DB::transaction(function () use ($data, $allergies, $emergencyContacts) {
            // Generate unique lifelong MRN
            $data['medical_record_number'] = Patient::generateNextMrn();
            $data['created_by'] = auth()->id();

            // Check if NIK already exists for verified patient
            if (! empty($data['nik'])) {
                $existing = Patient::where('nik', $data['nik'])->first();
                if ($existing) {
                    throw ValidationException::withMessages([
                        'nik' => "Pasien dengan NIK {$data['nik']} sudah terdaftar dengan No. RM {$existing->medical_record_number}.",
                    ]);
                }
            }

            $patient = Patient::create($data);

            // Save allergies
            if (! empty($allergies)) {
                foreach ($allergies as $allergy) {
                    if (! empty($allergy['allergen_name'])) {
                        $patient->allergies()->create([
                            'allergen_type' => $allergy['allergen_type'] ?? 'obat',
                            'allergen_name' => $allergy['allergen_name'],
                            'reaction' => $allergy['reaction'] ?? null,
                            'severity' => $allergy['severity'] ?? 'sedang',
                            'notes' => $allergy['notes'] ?? null,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // Save emergency contacts
            if (! empty($emergencyContacts)) {
                foreach ($emergencyContacts as $contact) {
                    if (! empty($contact['name']) && ! empty($contact['phone'])) {
                        $patient->emergencyContacts()->create([
                            'name' => $contact['name'],
                            'relationship' => $contact['relationship'] ?? 'Keluarga',
                            'phone' => $contact['phone'],
                            'address' => $contact['address'] ?? null,
                        ]);
                    }
                }
            }

            AuditLog::log('PATIENT_CREATED', 'PATIENTS', "Pendaftaran pasien baru: {$patient->name} ({$patient->medical_record_number})", null, $patient->toArray());

            return $patient;
        });
    }

    /**
     * Update existing patient information.
     */
    public function updatePatient(Patient $patient, array $data, ?array $allergies = null, ?array $emergencyContacts = null): Patient
    {
        return DB::transaction(function () use ($patient, $data, $allergies, $emergencyContacts) {
            $oldValues = $patient->toArray();

            if (! empty($data['nik']) && $data['nik'] !== $patient->nik) {
                $existing = Patient::where('nik', $data['nik'])->where('id', '!=', $patient->id)->first();
                if ($existing) {
                    throw ValidationException::withMessages([
                        'nik' => "Pasien dengan NIK {$data['nik']} sudah terdaftar dengan No. RM {$existing->medical_record_number}.",
                    ]);
                }
            }

            $patient->update($data);

            if ($allergies !== null) {
                $patient->allergies()->delete();
                foreach ($allergies as $allergy) {
                    if (! empty($allergy['allergen_name'])) {
                        $patient->allergies()->create([
                            'allergen_type' => $allergy['allergen_type'] ?? 'obat',
                            'allergen_name' => $allergy['allergen_name'],
                            'reaction' => $allergy['reaction'] ?? null,
                            'severity' => $allergy['severity'] ?? 'sedang',
                            'notes' => $allergy['notes'] ?? null,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }

            if ($emergencyContacts !== null) {
                $patient->emergencyContacts()->delete();
                foreach ($emergencyContacts as $contact) {
                    if (! empty($contact['name']) && ! empty($contact['phone'])) {
                        $patient->emergencyContacts()->create([
                            'name' => $contact['name'],
                            'relationship' => $contact['relationship'] ?? 'Keluarga',
                            'phone' => $contact['phone'],
                            'address' => $contact['address'] ?? null,
                        ]);
                    }
                }
            }

            AuditLog::log('PATIENT_UPDATED', 'PATIENTS', "Pembaruan identitas pasien: {$patient->name} ({$patient->medical_record_number})", $oldValues, $patient->fresh()->toArray());

            return $patient;
        });
    }
}
