<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Http\Resources\PatientResource;
use App\Http\Resources\AppointmentResource;

class PatientController extends Controller
{

    public $patientRepository;

    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function index()
    {
        try {
            $patients = $this->patientRepository->paginate();
            return PatientResource::collection($patients);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $patient = $this->patientRepository->find($id);
            if (!$patient) {
                return response()->json(['error' => 'Patient not found'], HttpResponse::HTTP_NOT_FOUND);
            }
            return new PatientResource($patient);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StorePatientRequest $request)
    {
        try {
            $patient = $this->patientRepository->store($request->validated());
            if (!$patient) {
                return response()->json(['error' => 'Failed to create patient'], HttpResponse::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'status' => true,
                'message' => 'Patient created successfully',
                'data' => new PatientResource($patient)
            ], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            \Log::error('Patient Store Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Patient creation failed: ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdatePatientRequest $request, string $id)
    {
        try {
            $patient = $this->patientRepository->update($request->validated(), $id);
            if (!$patient) {
                return response()->json(['error' => 'Failed to update patient'], HttpResponse::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'status' => true,
                'message' => 'Patient updated successfully',
                'data' => new PatientResource($patient)
            ]);
        } catch (\Exception $e) {
            \Log::error('Patient Update Error: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Patient update failed: ' . $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $patients = $this->patientRepository->destroy($id);
            if (!$patients) {
                return response()->json(['error' => 'Failed to delete patient'], HttpResponse::HTTP_BAD_REQUEST);
            }
            return response()->json(['status' => true, 'message' => 'Patient deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function appointments($id)
    {
        try {
            $appointments = $this->patientRepository->appointments($id);
            if (!$appointments) {
                return response()->json(['error' => 'Appointments not found'], HttpResponse::HTTP_NOT_FOUND);
            }
            return AppointmentResource::collection($appointments);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
