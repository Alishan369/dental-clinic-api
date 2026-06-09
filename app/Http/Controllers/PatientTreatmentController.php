<?php

namespace App\Http\Controllers;

use App\Models\PatientTreatment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PatientTreatmentController extends Controller
{
    /**
     * GET /api/v1/patients/{patientId}/treatments
     * Get treatments of a patient
     */
    public function index($patientId)
    {
        try {
            $patient = Patient::findOrFail($patientId);
            $treatments = $patient->patientTreatments()
                ->with(['treatmentType', 'doctor.user', 'appointment'])
                ->latest()
                ->get();
            return $this->successResponse($treatments, 'Patient treatments retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/patients/{patientId}/treatments
     * Add a treatment for a patient
     */
    public function store(Request $request, $patientId)
    {
        try {
            $patient = Patient::findOrFail($patientId);

            $validator = Validator::make($request->all(), [
                'treatment_type_id' => 'required|exists:treatment_types,id',
                'doctor_id'         => 'required|exists:doctors,id',
                'cost'              => 'required|numeric|min:0',
                'notes'             => 'nullable|string',
                'appointment_id'    => 'nullable|exists:appointments,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $data = $validator->validated();
            $data['patient_id'] = $patientId;

            $treatment = PatientTreatment::create($data);
            
            // Reload with relations
            $treatment->load(['treatmentType', 'doctor.user', 'appointment']);

            return $this->successResponse($treatment, 'Patient treatment added successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
