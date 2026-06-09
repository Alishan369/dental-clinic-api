<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Payment::with(['patient', 'treatment.doctor.user']);

        if ($this->request->start_date) {
            $query->whereDate('created_at', '>=', $this->request->start_date);
        }
        if ($this->request->end_date) {
            $query->whereDate('created_at', '<=', $this->request->end_date);
        }
        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }
        if ($this->request->patient_id) {
            $query->where('patient_id', $this->request->patient_id);
        }

        return $query->get();
    }

    public function map($payment): array
    {
        return [
            $payment->created_at->format('Y-m-d'),
            $payment->patient->name ?? 'N/A',
            $payment->final_amount,
            $payment->payment_method,
            $payment->status,
            $payment->treatment->doctor->user->name ?? 'N/A' // Assuming received by doctor handling the treatment
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Patient',
            'Amount',
            'Method',
            'Status',
            'Received By'
        ];
    }
}
