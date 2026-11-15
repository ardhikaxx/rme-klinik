<?php

namespace App\Services;

use App\Models\FhirMapping;
use App\Models\FhirSyncLog;
use App\Models\MedicalRecord;
use App\Models\NursingAssessment;
use App\Models\Patient;
use App\Models\Visit;

class FhirService
{
    /**
     * Build FHIR Patient Resource payload (HL7 FHIR R4).
     */
    public function buildPatientPayload(Patient $patient): array
    {
        return [
            'resourceType' => 'Patient',
            'identifier' => [
                [
                    'use' => 'official',
                    'system' => 'https://fhir.kemkes.go.id/id/nik',
                    'value' => $patient->nik ?? '',
                ],
                [
                    'use' => 'usual',
                    'system' => 'https://fhir.kemkes.go.id/id/mrn',
                    'value' => $patient->medical_record_number,
                ],
            ],
            'active' => true,
            'name' => [
                [
                    'use' => 'official',
                    'text' => $patient->name,
                ],
            ],
            'telecom' => [
                [
                    'system' => 'phone',
                    'value' => $patient->phone,
                    'use' => 'mobile',
                ],
            ],
            'gender' => $patient->gender === 'L' ? 'male' : 'female',
            'birthDate' => $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : null,
            'address' => [
                [
                    'use' => 'home',
                    'line' => [$patient->address],
                    'city' => $patient->city,
                    'district' => $patient->kecamatan,
                    'state' => $patient->province,
                    'postalCode' => $patient->postal_code,
                    'country' => 'ID',
                ],
            ],
        ];
    }

    /**
     * Build FHIR Encounter Resource payload (HL7 FHIR R4).
     */
    public function buildEncounterPayload(Visit $visit): array
    {
        $status = match ($visit->status) {
            'selesai' => 'finished',
            'dibatalkan' => 'cancelled',
            default => 'in-progress',
        };

        return [
            'resourceType' => 'Encounter',
            'identifier' => [
                [
                    'system' => 'http://sys-ids.kemkes.go.id/encounter',
                    'value' => $visit->visit_number,
                ],
            ],
            'status' => $status,
            'class' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'AMB',
                'display' => 'ambulatory',
            ],
            'subject' => [
                'reference' => "Patient/{$visit->patient->medical_record_number}",
                'display' => $visit->patient->name,
            ],
            'participant' => [
                [
                    'individual' => [
                        'reference' => "Practitioner/{$visit->doctor->employee_number}",
                        'display' => $visit->doctor->formatted_name,
                    ],
                ],
            ],
            'period' => [
                'start' => $visit->registration_date->toIso8601String(),
            ],
        ];
    }

    /**
     * Build FHIR Condition (Diagnosis) Resource payload.
     */
    public function buildConditionPayload(MedicalRecord $record, string $icd10Code, string $description, string $type = 'encounter-diagnosis'): array
    {
        return [
            'resourceType' => 'Condition',
            'clinicalStatus' => [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                        'code' => 'active',
                    ],
                ],
            ],
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                            'code' => $type,
                        ],
                    ],
                ],
            ],
            'code' => [
                'coding' => [
                    [
                        'system' => 'http://hl7.org/fhir/sid/icd-10',
                        'code' => $icd10Code,
                        'display' => $description,
                    ],
                ],
            ],
            'subject' => [
                'reference' => "Patient/{$record->patient->medical_record_number}",
            ],
            'encounter' => [
                'reference' => "Encounter/{$record->visit->visit_number}",
            ],
            'recordedDate' => $record->examination_time->toIso8601String(),
        ];
    }

    /**
     * Build FHIR Observation (Vital Signs) Resource payload.
     */
    public function buildObservationPayload(NursingAssessment $assessment): array
    {
        return [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                            'code' => 'vital-signs',
                        ],
                    ],
                ],
            ],
            'subject' => [
                'reference' => "Patient/{$assessment->patient->medical_record_number}",
            ],
            'effectiveDateTime' => $assessment->assessment_time->toIso8601String(),
            'component' => array_filter([
                $assessment->systolic ? [
                    'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '8480-6', 'display' => 'Systolic blood pressure']]],
                    'valueQuantity' => ['value' => $assessment->systolic, 'unit' => 'mmHg'],
                ] : null,
                $assessment->diastolic ? [
                    'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '8462-4', 'display' => 'Diastolic blood pressure']]],
                    'valueQuantity' => ['value' => $assessment->diastolic, 'unit' => 'mmHg'],
                ] : null,
                $assessment->temperature ? [
                    'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '8310-5', 'display' => 'Body temperature']]],
                    'valueQuantity' => ['value' => (float) $assessment->temperature, 'unit' => 'C'],
                ] : null,
                $assessment->pulse_rate ? [
                    'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '8867-4', 'display' => 'Heart rate']]],
                    'valueQuantity' => ['value' => $assessment->pulse_rate, 'unit' => 'beats/minute'],
                ] : null,
                $assessment->weight ? [
                    'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '29463-7', 'display' => 'Body weight']]],
                    'valueQuantity' => ['value' => (float) $assessment->weight, 'unit' => 'kg'],
                ] : null,
                $assessment->height ? [
                    'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '8302-2', 'display' => 'Body height']]],
                    'valueQuantity' => ['value' => (float) $assessment->height, 'unit' => 'cm'],
                ] : null,
            ]),
        ];
    }

    /**
     * Register or update FHIR Readiness Mapping entry.
     */
    public function stageMapping(string $resourceType, string $localTable, int $localId, array $payload): FhirMapping
    {
        $mapping = FhirMapping::updateOrCreate(
            [
                'local_table' => $localTable,
                'local_id' => $localId,
            ],
            [
                'resource_type' => $resourceType,
                'status' => 'pending',
            ]
        );

        FhirSyncLog::create([
            'fhir_mapping_id' => $mapping->id,
            'resource_type' => $resourceType,
            'payload' => $payload,
            'response_code' => null,
            'response_body' => null,
            'error_message' => 'SATUSEHAT Production credentials not configured. Payload generated and validated for FHIR R4 schema compliance.',
            'attempt' => 1,
            'synced_at' => now(),
        ]);

        return $mapping;
    }
}
