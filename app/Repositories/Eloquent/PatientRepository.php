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
    public function paginate(array $request): LengthAwarePaginator
    {
        $search = $request['search'] ?? null;
        $perPage = $request['per_page'] ?? 20;

        $patients = Patient::with(['appointments.doctor', 'diseases'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate($perPage);

        return $patients;
    }

    public function find(string $id): ?Patient
    {
        return Patient::with(['appointments.doctor', 'diseases', 'payments'])->find($id);
    }

    public function store(array $requestData): Patient
    {
            $patientCode = 'PAT-' . str_pad(Patient::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT);
            while (Patient::where('patient_code', $patientCode)->exists()) {
                $patientCode = 'PAT-' . strtoupper(bin2hex(random_bytes(3)));
            }

            $patient = Patient::create([
                'patient_code' => $patientCode,
                'name'         => $requestData['name'],
                'email'        => $requestData['email'] ?? null,
                'phone'        => $requestData['phone'] ?? null,
                'address'      => $requestData['address'] ?? null,
                'dob'          => $requestData['dob'] ?? null,
                'gender'       => $requestData['gender'] ?? null,
            ]);

            if (isset($requestData['disease_ids']) && is_array($requestData['disease_ids'])) {
                $patient->diseases()->sync($requestData['disease_ids']);
            }

            return $patient;
    }

    public function update(array $requestData, string $id): Patient
    {
        $patient = Patient::findOrFail($id);
        $patient->update([
            'name'    => $requestData['name'] ?? $patient->name,
            'email'   => $requestData['email'] ?? $patient->email,
            'phone'   => $requestData['phone'] ?? $patient->phone,
            'address' => $requestData['address'] ?? $patient->address,
            'dob'     => $requestData['dob'] ?? $patient->dob,
            'gender'  => $requestData['gender'] ?? $patient->gender,
        ]);

        if (isset($requestData['disease_ids']) && is_array($requestData['disease_ids'])) {
            $patient->diseases()->sync($requestData['disease_ids']);
        }

        return $patient;
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
