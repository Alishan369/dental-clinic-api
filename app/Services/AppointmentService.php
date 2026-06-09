<?php

namespace App\Services;

use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Exception;

class AppointmentService
{
    protected $appointmentRepository;

    public function __construct(AppointmentRepositoryInterface $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getAll()
    {
        return $this->appointmentRepository->index();
    }

    public function getById($id)
    {
        return $this->appointmentRepository->show($id);
    }

    public function store(array $data)
    {
        // Basic check for conflict (this should ideally be querying DB for same doctor_id and time)
        // Since we don't have the specific time columns defined in the prompt, we proceed with base storing
        return $this->appointmentRepository->store($data);
    }

    public function update($id, array $data)
    {
        return $this->appointmentRepository->update($data, $id);
    }

    public function destroy($id)
    {
        return $this->appointmentRepository->destroy($id);
    }
}
