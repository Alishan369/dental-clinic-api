<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->latest()->get();

        return $this->successResponse(
            AppointmentResource::collection($appointments),
            'Appointments retrieved successfully'
        );
    }
    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        $appointmentDate = $validated['appointment_date'];

        $schedule = $this->findDoctorSchedule(
            $validated['doctor_id'],
            Carbon::parse($appointmentDate)->dayOfWeek
        );

        if (!$schedule) {
            $dayName = Carbon::parse($appointmentDate)->format('l');
            return $this->errorResponse("Doctor not available on {$dayName}", 422);
        }

        $startTime = $this->parseTimeOnDate($appointmentDate, $validated['start_time']);
        $scheduleStart = $this->parseTimeOnDate($appointmentDate, $schedule->start_time);
        $scheduleEnd = $this->parseTimeOnDate($appointmentDate, $schedule->end_time);

        if ($startTime->isToday() && $startTime->lte(now())) {
            return $this->errorResponse('Cannot book a slot in the past', 422);
        }

        if ($startTime->lt($scheduleStart) || $startTime->gte($scheduleEnd)) {
            return $this->errorResponse(
                "Time must be between {$scheduleStart->format('H:i')} and {$scheduleEnd->format('H:i')}",
                422
            );
        }

        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $slotDuration = (int) ($doctor->slot_duration ?? 30);
        $endTime = $startTime->copy()->addMinutes($slotDuration);

        if ($endTime->gt($scheduleEnd)) {
            $lastSlot = $scheduleEnd->copy()->subMinutes($slotDuration)->format('H:i');
            return $this->errorResponse(
                "Slot exceeds working hours. Last available slot starts at {$lastSlot}",
                422
            );
        }

        if ($this->hasOverlappingAppointment(
            $validated['doctor_id'],
            $appointmentDate,
            $startTime,
            $endTime
        )) {
            return $this->errorResponse(
                "Slot {$startTime->format('H:i')} - {$endTime->format('H:i')} is already booked",
                422
            );
        }

        try {
            $appointment = DB::transaction(function () use ($validated, $startTime, $endTime) {
                return Appointment::create([
                    'patient_id'       => $validated['patient_id'],
                    'doctor_id'        => $validated['doctor_id'],
                    'appointment_date' => $validated['appointment_date'],
                    'start_time'       => $startTime->format('H:i:s'),
                    'end_time'         => $endTime->format('H:i:s'),
                    'status'           => 'scheduled',
                    'type'             => $validated['type'],
                    'notes'            => $validated['notes'] ?? null,
                    'created_by'       => auth()->id(),
                ]);
            });

            return $this->successResponse(
                new AppointmentResource($appointment->load(['patient', 'doctor.user'])),
                'Appointment booked successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Appointment creation failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to create appointment', 500);
        }
    }

    private function findDoctorSchedule(string $doctorId, int $dayOfWeek): ?DoctorSchedule
    {
        return DoctorSchedule::where('doctor_id', $doctorId)
            ->where(function ($query) use ($dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek);
                // Legacy schedules saved with Sunday = 7 from older app builds
                if ($dayOfWeek === 0) {
                    $query->orWhere('day_of_week', 7);
                }
            })
            ->where('is_available', true)
            ->first();
    }

    private function parseTimeOnDate(string $date, mixed $time): Carbon
    {
        if ($time instanceof Carbon) {
            $time = $time->format('H:i:s');
        }

        $timeString = (string) $time;
        if (strlen($timeString) === 5) {
            $timeString .= ':00';
        }

        return Carbon::parse("{$date} {$timeString}");
    }

    private function hasOverlappingAppointment(
        string $doctorId,
        string $appointmentDate,
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        return Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $appointmentDate)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('start_time', '<', $endTime->format('H:i:s'))
            ->where('end_time', '>', $startTime->format('H:i:s'))
            ->exists();
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->find($id);

        if (!$appointment) {
            return $this->errorResponse('Appointment not found', 404);
        }

        return $this->successResponse(
            new AppointmentResource($appointment),
            'Appointment retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return $this->errorResponse('Appointment not found', 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|required|in:scheduled,confirmed,completed,cancelled,no_show',
            'notes'  => 'nullable|string',
        ]);

        try {
            $appointment->update($validated);

            return $this->successResponse(
                new AppointmentResource($appointment->load(['patient', 'doctor'])),
                'Appointment updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to update appointment: " . $e->getMessage(), 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return $this->errorResponse('Appointment not found', 404);
        }

        $validated = $request->validate([
            'cancelled_reason' => 'required|string|max:255',
        ]);

        try {
            $appointment->update([
                'status'           => 'cancelled',
                'cancelled_reason' => $validated['cancelled_reason'],
            ]);

            return $this->successResponse(
                new AppointmentResource($appointment->load(['patient', 'doctor'])),
                'Appointment cancelled successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to cancel appointment: " . $e->getMessage(), 500);
        }
    }

    public function getByPatient($patientId)
    {
        $patient = Patient::find($patientId);
        if (!$patient) {
            return $this->errorResponse('Patient not found', 404);
        }

        $appointments = Appointment::with(['patient', 'doctor'])
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        return $this->successResponse(
            AppointmentResource::collection($appointments),
            'Patient appointments retrieved successfully'
        );
    }

    public function getToday()
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', today())
            ->latest()
            ->get();

        return $this->successResponse(
            AppointmentResource::collection($appointments),
            "Today's appointments retrieved successfully"
        );
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return $this->errorResponse('Appointment not found', 404);
        }

        try {
            $appointment->delete();
            return $this->successResponse(
                null,
                'Appointment deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to delete appointment: " . $e->getMessage(), 500);
        }
    }
}

