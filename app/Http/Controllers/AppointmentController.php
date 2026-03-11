<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Http\Resources\AppointmentResource;

class AppointmentController extends Controller
{
    public AppointmentRepositoryInterface $appointmentRepository;

    public function __construct(
        AppointmentRepositoryInterface $appointmentRepository
    ) {
        $this->appointmentRepository = $appointmentRepository;
    }
    public function index()
    {
        $data = $this->appointmentRepository->index();
        return response()->json([
            'status' => true,
            'data' => AppointmentResource::collection($data),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $data = $this->appointmentRepository->store($request->all());
    //     return response()->json([
    //         'status' => true,
    //         'data' => AppointmentResource::make($data),
    //     ], 200);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Appointment $appointment)
    // {
    //     $data = $this->appointmentRepository->show($appointment->id);
    //     return response()->json([
    //         'status' => true,
    //         'data' => AppointmentResource::make($data),
    //     ], 200);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Appointment $appointment)
    // {
    //     $data = $this->appointmentRepository->update($request->all(), $appointment->id);
    //     return response()->json([
    //         'status' => true,
    //         'data' => AppointmentResource::make($data),
    //     ], 200);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Appointment $appointment)
    // {
    //     $data = $this->appointmentRepository->destroy($appointment->id);
    //     return response()->json([
    //         'status' => true,
    //         'data' => AppointmentResource::make($data),
    //     ], 200);
    // }
}
