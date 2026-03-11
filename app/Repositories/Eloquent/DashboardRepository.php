<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Disease;
use App\Models\User;
use App\Models\Role;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function index(): array
    {
        $doctorRoleId = Role::where('name', 'doctor')->value('id');

        $totalPatients = Patient::count();
        $todayAppointments = Appointment::whereDate('appointment_date', today())->count();
        $totalDiseases = Disease::where('created_at', '>=', today())->count();
        $totalDoctors = $doctorRoleId ? User::where('role_id', $doctorRoleId)->count() : 0;
        $recentAppointments = Appointment::with(['patient', 'doctor'])->latest()->take(5)->get();

        return [
            'total_patients' => $totalPatients,
            'today_appointments' => $todayAppointments,
            'today_diseases' => $totalDiseases,
            'total_doctors' => $totalDoctors,
            'recent_appointments' => $recentAppointments,
        ];
    }
}