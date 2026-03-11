<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\DiseaseRepositoryInterface;
use App\Http\Resources\DiseaseResource;

class DiseaseController extends Controller
{
    public $diseaseRepository;

    public function __construct(DiseaseRepositoryInterface $diseaseRepository)
    {
        $this->diseaseRepository = $diseaseRepository;
    }
    
    public function index()
    {
        $diseases = $this->diseaseRepository->all();
        return response()->json([
            'status' => true,
            'data' => DiseaseResource::collection($diseases),
        ], 200);
    }
}
