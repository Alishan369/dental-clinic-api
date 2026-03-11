<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AppointmentResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_patients' => $this->resource['total_patients'] ?? 0,
            'active_doctors' => $this->resource['total_doctors'] ?? 0,
            'today_appointments' => $this->resource['today_appointments'] ?? 0,
            'today_diseases' => $this->resource['today_diseases'] ?? 0,
            'recent_appointments' => AppointmentResource::collection($this->resource['recent_appointments'] ?? []),
        ];
    }
}
