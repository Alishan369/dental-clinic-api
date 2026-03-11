<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Http\Resources\DashboardResource;

class DashboardController extends Controller
{
    public DashboardRepositoryInterface $dashboardRepository;
    public function __construct(
        DashboardRepositoryInterface $dashboardRepository
    ) {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function index()
    {
        $data = $this->dashboardRepository->index();
        return response()->json([
            'status' => true,
             'data' => DashboardResource::make($data),
        ], 200);
    }
}
