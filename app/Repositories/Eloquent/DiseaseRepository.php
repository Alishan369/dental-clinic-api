<?php

namespace App\Repositories\Eloquent;

use App\Models\Disease;
use App\Repositories\Contracts\DiseaseRepositoryInterface;

class DiseaseRepository implements DiseaseRepositoryInterface
{
    protected $model;

    public function __construct(Disease $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }
}