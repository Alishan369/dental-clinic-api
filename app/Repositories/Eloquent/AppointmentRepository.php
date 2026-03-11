<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Models\Appointment;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function index()
    {
        return Appointment::with('patient', 'doctor')->get();
    }

    // public function store(array $request)
    // {
    //     return Appointment::create($request);
    // }

    // public function show(string $id)
    // {
    //     return Appointment::with('patient', 'doctor')->find($id);
    // }

    // public function update(array $request, string $id)
    // {
    //     $appointment = Appointment::find($id);
    //     $appointment->update($request);
    //     return $appointment;
    // }

    // public function destroy(string $id)
    // {
    //     return Appointment::destroy($id);
    // }
}