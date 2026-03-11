<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface PatientRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function find(string $id);

    public function store(array $request);

    public function update(array $request, string $id);

    public function destroy(string $id) : bool;
}
