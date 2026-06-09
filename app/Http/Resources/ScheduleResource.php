<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'day_of_week' => $this->day_of_week,
            'day_name' => $dayNames[$this->day_of_week],
            'start_time' => $this->start_time instanceof \Carbon\Carbon ? $this->start_time->format('H:i') : (is_string($this->start_time) ? substr($this->start_time, 0, 5) : $this->start_time),
            'end_time' => $this->end_time instanceof \Carbon\Carbon ? $this->end_time->format('H:i') : (is_string($this->end_time) ? substr($this->end_time, 0, 5) : $this->end_time),
            'is_available' => $this->is_available,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
