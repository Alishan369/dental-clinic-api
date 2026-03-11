<?php

namespace App\Repositories\Eloquent;

use App\Models\Patient;
use App\Models\Appointment;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PatientRepository implements PatientRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Patient::with(['appointments.doctor', 'diseases'])->latest()->paginate($perPage);
    }

    public function find(string $id): ?Patient
    {
        return Patient::with(['appointments.doctor', 'diseases'])->find($id);
    }

    public function store(array $requestData): Patient
    {
        return DB::transaction(function () use ($requestData) {
            $patient = Patient::create([
                'name'    => $requestData['name'],
                'email'   => $requestData['email'] ?? null,
                'phone'   => $requestData['phone'] ?? null,
                'address' => $requestData['address'] ?? null,
            ]);

            if (!empty($requestData['appointments'])) {
                foreach ($requestData['appointments'] as $appointment) {
                    $patient->appointments()->create([
                        'doctor_id'        => $appointment['doctor_id'],
                        'appointment_date' => $appointment['appointment_date'],
                        'appointment_time' => $appointment['appointment_time'] ?? null,
                        'status'           => 'scheduled',
                    ]);
                }
            }

            if (!empty($requestData['diseases'])) {
                $diseaseData = [];
                foreach ($requestData['diseases'] as $disease) {
                    $diseaseData[$disease['id']] = [
                        'notes' => $disease['notes'] ?? null,
                    ];
                }
                $patient->diseases()->sync($diseaseData);
            }

            return $patient->load('appointments.doctor', 'diseases');
        });
    }

    public function update(array $requestData, string $id): Patient
    {
        return DB::transaction(function () use ($requestData, $id) {
            $patient = Patient::findOrFail($id);

            $patient->update([
                'name'    => $requestData['name'] ?? $patient->name,
                'email'   => $requestData['email'] ?? $patient->email,
                'phone'   => $requestData['phone'] ?? $patient->phone,
                'address' => $requestData['address'] ?? $patient->address,
            ]);

            if (isset($requestData['appointments'])) {
                // Clear existing appointments for this patient if we're syncing
                $patient->appointments()->delete();

                foreach ($requestData['appointments'] as $appointment) {
                    $patient->appointments()->create([
                        'doctor_id'        => $appointment['doctor_id'],
                        'appointment_date' => $appointment['appointment_date'],
                        'appointment_time' => $appointment['appointment_time'] ?? null,
                        'status'           => 'scheduled',
                    ]);
                }
            }

            if (isset($requestData['diseases'])) {
                $diseaseData = [];
                foreach ($requestData['diseases'] as $disease) {
                    $diseaseData[$disease['id']] = [
                        'notes' => $disease['notes'] ?? null,
                    ];
                }
                $patient->diseases()->sync($diseaseData);
            }

            return $patient->load('appointments.doctor', 'diseases');
        });
    }

    public function destroy(string $id): bool
    {
        $patient = Patient::findOrFail($id);
        return (bool) $patient->delete();
    }

    public function appointments(string $id) : Collection
    {
        return Patient::findOrFail($id)->appointments()->with('doctor')->get();
    }
}
