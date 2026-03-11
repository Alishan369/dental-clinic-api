<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\DoctorRepositoryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Support\Collection;
use App\Models\Appointment;

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
        return User::where('role_id', $this->doctorRoleId)->paginate($perPage);
    }

    public function find(string $id): ?User
    {
        return User::where('role_id', $this->doctorRoleId)->find($id);
    }

    public function store(array $requestData)
    {
        if (!$this->doctorRoleId) {
            throw new \Exception("The 'doctor' role was not found in the database. Please seed your roles.");
        }

        return User::create([
            'name'       => $requestData['name'],
            'email'      => $requestData['email'],
            'password'   => Hash::make($requestData['password']),
            'phone'      => $requestData['phone'] ?? null,
            'address'    => $requestData['address'] ?? null,
            'experience' => $requestData['experience'] ?? null,
            'role_id'    => $this->doctorRoleId,
        ]);
    }

    public function update(array $requestData, string $id)
    {
        $user = User::findOrFail($id);

        if (isset($requestData['password'])) {
            $requestData['password'] = Hash::make($requestData['password']);
        }
        
        $user->update($requestData);

        return $user;
    }

    public function destroy(string $id): bool
    {
        return (bool) User::where('id', $id)->delete();
    }

    public function appointments(string $id) : Collection
    {
        return Appointment::where('doctor_id', $id)->with('patient')->get();
    }
}
