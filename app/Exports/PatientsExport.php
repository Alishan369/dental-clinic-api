<?php

namespace App\Exports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PatientsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Patient::query();

        if ($this->request->start_date) {
            $query->whereDate('created_at', '>=', $this->request->start_date);
        }
        if ($this->request->end_date) {
            $query->whereDate('created_at', '<=', $this->request->end_date);
        }

        return $query->get();
    }

    public function map($patient): array
    {
        return [
            $patient->patient_code,
            $patient->name,
            $patient->phone,
            $patient->email,
            $patient->gender,
            $patient->dob ? $patient->dob->format('Y-m-d') : 'N/A',
            $patient->created_at->format('Y-m-d'),
        ];
    }

    public function headings(): array
    {
        return [
            'Patient Code',
            'Name',
            'Phone',
            'Email',
            'Gender',
            'DOB',
            'Registration Date'
        ];
    }
}
