<?php

namespace App\Exports;

use App\Models\Appointment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppointmentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Appointment::with(['patient', 'doctor.user']);

        if ($this->request->start_date) {
            $query->whereDate('appointment_date', '>=', $this->request->start_date);
        }
        if ($this->request->end_date) {
            $query->whereDate('appointment_date', '<=', $this->request->end_date);
        }
        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }
        if ($this->request->doctor_id) {
            $query->where('doctor_id', $this->request->doctor_id);
        }

        return $query->get();
    }

    public function map($appointment): array
    {
        return [
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->formatted_time ?? ($appointment->appointment_time ?? 'N/A'),
            $appointment->patient?->name ?? 'N/A',
            $appointment->doctor?->user?->name ?? 'N/A',   // Doctor name from users table
            $appointment->status,
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Time',
            'Patient',
            'Doctor',
            'Status'
        ];
    }
}
