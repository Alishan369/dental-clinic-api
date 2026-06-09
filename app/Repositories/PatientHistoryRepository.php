<?php

namespace App\Repositories;

use App\Models\PatientHistory;

class PatientHistoryRepository
{
    public function getByPatient($patientId)
    {
        return PatientHistory::where('patient_id', $patientId)
            ->with(['doctor' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('treatment_date', 'desc')
            ->get();
    }

    public function store(array $data)
    {
        return PatientHistory::create($data);
    }
}
