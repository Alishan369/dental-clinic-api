<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Doctor;

interface DoctorRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function find(string $id) : Doctor;

    public function store(array $request);

    public function update(array $request, string $id);

    public function destroy(string $id) : bool;

    public function appointments(string $id) : Collection;

    public function getSchedules(string $id);

    public function storeSchedule(string $id, array $data);

    public function updateSchedule(string $id, string $scheduleId, array $data);

    public function deleteSchedule(string $id, string $scheduleId): bool;
}
