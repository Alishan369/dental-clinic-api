<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\ScheduleResource;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct(private DoctorRepositoryInterface $doctorRepository) {}

    public function index()
    {
        try {
            $doctors = $this->doctorRepository->paginate();
            return $this->successResponse(DoctorResource::collection($doctors), 'Doctors fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(string $id)
    {
        try {
            $doctor = $this->doctorRepository->find($id);
            if (!$doctor) {
                return $this->errorResponse('Doctor not found', HttpResponse::HTTP_NOT_FOUND);
            }
            return $this->successResponse(new DoctorResource($doctor), 'Doctor fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreDoctorRequest $request)
    {
        try {
            $doctor = $this->doctorRepository->store($request->validated());
            if (!$doctor) {
                return $this->errorResponse('Failed to create doctor', HttpResponse::HTTP_BAD_REQUEST);
            }
            return $this->successResponse(new DoctorResource($doctor), 'Doctor created successfully', HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse('Doctor creation failed: ' . $e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateDoctorRequest $request, string $id)
    {
        try {
            $doctor = $this->doctorRepository->update($request->validated(), $id);
            if (!$doctor) {
                return $this->errorResponse('Failed to update doctor', HttpResponse::HTTP_BAD_REQUEST);
            }
            return $this->successResponse(new DoctorResource($doctor), 'Doctor updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(string $id)
    {
        try {
            $result = $this->doctorRepository->destroy($id);
            if (!$result) {
                return $this->errorResponse('Failed to delete doctor', HttpResponse::HTTP_BAD_REQUEST);
            }
            return $this->successResponse(null, 'Doctor deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function appointments(string $id)
    {
        try {
            $appointments = $this->doctorRepository->appointments($id);
            return $this->successResponse(AppointmentResource::collection($appointments), 'Appointments fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSchedule(string $doctorId)
    {
        try {
            $schedules = $this->doctorRepository->getSchedules($doctorId);
            if (!$schedules) {
                return $this->errorResponse('Doctor not found', HttpResponse::HTTP_NOT_FOUND);
            }
            return $this->successResponse(ScheduleResource::collection($schedules), 'Doctor schedule fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeSchedule(Request $request, string $id)
    {
        try {
            // Also accept H:i:s format

            $validated = $request->validate([
                'day_of_week' => 'required|integer|between:0,6',
                'start_time' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
                'end_time' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', 'after:start_time'],
                'is_available' => 'boolean',
            ]);

            $schedule = $this->doctorRepository->storeSchedule($id, $validated);
            return $this->successResponse(new ScheduleResource($schedule), 'Schedule added successfully', HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateSchedule(Request $request, $id, $scheduleId)
    {
        try {

            $validated = $request->validate([
                'day_of_week' => 'required|integer|between:0,6',
                'start_time' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
                'end_time' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', 'after:start_time'],
                'is_available' => 'boolean',
            ]);

            $schedule = $this->doctorRepository->updateSchedule($id, $scheduleId, $validated);
            if (!$schedule) {
                return $this->errorResponse('Schedule not found', HttpResponse::HTTP_NOT_FOUND);
            }
            return $this->successResponse(new ScheduleResource($schedule), 'Schedule updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteSchedule($id, $scheduleId)
    {
        try {
            $result = $this->doctorRepository->deleteSchedule($id, $scheduleId);
            if (!$result) {
                return $this->errorResponse('Schedule not found', HttpResponse::HTTP_NOT_FOUND);
            }
            return $this->successResponse(null, 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
