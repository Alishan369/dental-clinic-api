<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DoctorsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $doctorRole = Role::where('name', 'doctor')->first();

        $query = User::with('doctor')
            ->where('role_id', $doctorRole?->id);

        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }

        return $query->get();
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->phone,
            $user->doctor?->specialization ?? 'N/A',
            $user->doctor?->experience_years ?? 0,
            $user->doctor?->license_number ?? 'N/A',
            $user->doctor?->commission_percentage ?? 0,
            $user->status,
            $user->created_at->format('Y-m-d'),
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Specialization',
            'Experience (Years)',
            'License Number',
            'Commission (%)',
            'Status',
            'Joined Date',
        ];
    }
}
