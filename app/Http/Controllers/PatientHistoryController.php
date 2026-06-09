<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PatientHistoryRepository;
use Illuminate\Support\Facades\Validator;
use Exception;

class PatientHistoryController extends Controller
{
    protected $historyRepo;

    public function __construct(PatientHistoryRepository $historyRepo)
    {
        $this->historyRepo = $historyRepo;
    }

    public function index($patientId)
    {
        try {
            $history = $this->historyRepo->getByPatient($patientId);
            return $this->successResponse($history, 'Patient history retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request, $patientId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|exists:users,id',
                'diagnosis' => 'required|string',
                'notes' => 'nullable|string',
                'treatment_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $data = $validator->validated();
            $data['patient_id'] = $patientId;

            $history = $this->historyRepo->store($data);
            return $this->successResponse($history, 'Patient history added successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
