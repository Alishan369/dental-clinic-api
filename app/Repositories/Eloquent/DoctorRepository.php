<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\DoctorRepositoryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Support\Collection;
use App\Models\Appointment;
use App\Models\Doctor;

class DoctorRepository implements DoctorRepositoryInterface
{
    protected ?string $doctorRoleId = null;

    public function __construct()
    {
        $role = Role::where('name', 'doctor')->first();
        if ($role) {
            $this->doctorRoleId = $role->id;
        }
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
     return Doctor::with(['user' => function ($query) {
            $query->where('role_id', $this->doctorRoleId);
        }])
        ->paginate($perPage);
    }

    public function find(string $id): Doctor
    {
        return Doctor::with('user')
        ->whereHas('user', function ($query) {
            $query->where('role_id', $this->doctorRoleId);
        })
        ->where('id', $id)
        ->first();
    }

    public function store(array $requestData)
    {
        if (!$this->doctorRoleId) {
            throw new \Exception("The 'doctor' role was not found in the database. Please seed your roles.");
        }

        $user = User::create([
            'name'     => $requestData['name'],
            'email'    => $requestData['email'],
            'password' => Hash::make($requestData['password']),
            'phone'    => $requestData['phone'] ?? null,
            'address'  => $requestData['address'] ?? null,
            'role_id'  => $this->doctorRoleId,
            'status'   => 'active',
        ]);

        $doctor = Doctor::create([
            'user_id'               => $user->id,
            'specialization'        => $requestData['specialization'] ?? null,
            'experience_years'      => $requestData['experience'] ?? $requestData['experience_years'] ?? 0,
            'license_number'        => $requestData['license_number'] ?? null,
            'commission_percentage' => $requestData['commission_percentage'] ?? 0,
        ]);

        return $doctor->load('user');
    }

    public function update(array $requestData, string $id)
    {
        try {
            $user = User::findOrFail($id);
            $userData = [];

            if (isset($requestData['name']))    $userData['name']    = $requestData['name'];
            if (isset($requestData['email']))   $userData['email']   = $requestData['email'];
            if (isset($requestData['phone']))   $userData['phone']   = $requestData['phone'];
            if (isset($requestData['address'])) $userData['address'] = $requestData['address'];
            if (isset($requestData['status']))  $userData['status']  = $requestData['status'];

            if (isset($requestData['password']) && !empty($requestData['password'])) {
                $userData['password'] = Hash::make($requestData['password']);
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            // Update or create the associated Doctor profile
            $doctorData = [];
            if (isset($requestData['specialization']))        $doctorData['specialization']        = $requestData['specialization'];
            if (isset($requestData['experience']))            $doctorData['experience_years']      = $requestData['experience'];
            if (isset($requestData['experience_years']))      $doctorData['experience_years']      = $requestData['experience_years'];
            if (isset($requestData['license_number']))        $doctorData['license_number']        = $requestData['license_number'];
            if (isset($requestData['commission_percentage'])) $doctorData['commission_percentage'] = $requestData['commission_percentage'];

            if (!empty($doctorData)) {
                $user->doctor()->updateOrCreate(
                    ['user_id' => $user->id],
                    $doctorData
                );
            }

            return $user->doctor->load('user');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(string $id): bool
    {
        return (bool) User::where('id', $id)->delete();
    }

    public function appointments(string $id) : Collection
    {
        return Appointment::where('doctor_id', $id)->with('patient')->get();
    }

    public function getSchedules(string $id)
    {
        $doctor = \App\Models\Doctor::find($id);
        if (!$doctor) {
            return null;
        }

        return $doctor->schedules()
            ->orderBy('day_of_week')
            ->get();
    }

    public function storeSchedule(string $id, array $data)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            throw new \Exception('Doctor profile not found for this user.');
        }

        $existing = $doctor->schedules()->where('day_of_week', $data['day_of_week'])->first();
        if ($existing) {
            $existing->update($data);
            return $existing->fresh();
        }

        return $doctor->schedules()->create($data);
    }

    public function updateSchedule(string $id, string $scheduleId, array $data)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return null;
        }
        if (!$doctor) {
            throw new \Exception('Doctor profile not found.');
        }

        $schedule = $doctor->schedules()->find($scheduleId);
        if (!$schedule) {
            \Log::warning("Schedule with ID $scheduleId not found for Doctor ID $id");
            return null;
        }

        $schedule->update($data);
        return $schedule->fresh();
    }

    public function deleteSchedule(string $id, string $scheduleId): bool
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return false;
        }

        // $doctor = $user->doctor;
        if (!$doctor) {
            return false;
        }

        return (bool) $doctor->schedules()->where('id', $scheduleId)->delete();
    }
}
