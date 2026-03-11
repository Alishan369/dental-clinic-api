<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DoctorRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function find(string $id);

    public function store(array $request);

    public function update(array $request, string $id);

    public function destroy(string $id) : bool;

    public function appointments(string $id) : Collection;
}
