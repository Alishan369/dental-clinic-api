<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\AppointmentResource;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DoctorController extends Controller
{

    public $doctorRepository;

    public function __construct(DoctorRepositoryInterface $doctorRepository)
    {
        $this->doctorRepository = $doctorRepository;
    }

    public function index()
    {
        try {
            $doctors = $this->doctorRepository->paginate();
            return DoctorResource::collection($doctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $doctor = $this->doctorRepository->find($id);
            if (!$doctor) {
                return response()->json(['error' => 'Doctor not found'], HttpResponse::HTTP_NOT_FOUND);
            }
            return new DoctorResource($doctor);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreDoctorRequest $request)
    {
        try {
            $doctor = $this->doctorRepository->store($request->validated());
            if (!$doctor) {
                return response()->json(['error' => 'Failed to create doctor'], HttpResponse::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'status' => true, 
                'message' => 'Doctor created successfully',
                'data' => new DoctorResource($doctor)
            ], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Doctor creation failed: ' . $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateDoctorRequest $request, string $id)
    {
        try {
            $doctors = $this->doctorRepository->update($request->validated(), $id);
            if (!$doctors) {
                return response()->json(['error' => 'Failed to update doctor'], HttpResponse::HTTP_BAD_REQUEST);
            }
            return response()->json(['status' => true, 'message' => 'Doctor updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $doctors = $this->doctorRepository->destroy($id);
            if (!$doctors) {
                return response()->json(['error' => 'Failed to delete doctor'], HttpResponse::HTTP_BAD_REQUEST);
            }
            return response()->json(['status' => true, 'message' => 'Doctor deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function appointments($id)
    {
        try {
            $appointments = $this->doctorRepository->appointments($id);
            return AppointmentResource::collection($appointments);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
