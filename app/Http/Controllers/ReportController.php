<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\Expense;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use App\Models\PatientTreatment;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PaymentsExport;
use App\Exports\AppointmentsExport;
use App\Exports\PatientsExport;
use App\Exports\DoctorsExport;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /api/v1/reports/daily-collection
     * Daily payment collection summary
     * Query: ?date=2024-01-15 (default: today)
     */
    public function dailyCollection(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        $collections = Payment::select('payment_method', DB::raw('SUM(paid_amount) as total'))
            ->whereDate('created_at', $date)
            ->groupBy('payment_method')
            ->get();

        $totalAmount = Payment::whereDate('created_at', $date)->sum('paid_amount');

        return $this->successResponse([
            'date'         => $date->format('Y-m-d'),
            'total_amount' => $totalAmount,
            'by_method'    => $collections,
        ], 'Daily collection fetched successfully');
    }

    /**
     * GET /api/v1/reports/monthly-summary
     * Monthly revenue, expenses, new patients, appointments summary
     * Query: ?month=1&year=2024
     */
    public function monthlySummary(Request $request)
    {
        $month = $request->month ?? Carbon::now()->month;
        $year  = $request->year  ?? Carbon::now()->year;

        $revenue = Payment::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('paid_amount');

        $expenses = Expense::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $newPatients = Patient::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        $totalAppointments = Appointment::whereMonth('appointment_date', $month)
            ->whereYear('appointment_date', $year)
            ->count();

        $completedTreatments = PatientTreatment::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        return $this->successResponse([
            'month'                => (int) $month,
            'year'                 => (int) $year,
            'total_revenue'        => (float) $revenue,
            'total_expenses'       => (float) $expenses,
            'net_profit'           => (float) ($revenue - $expenses),
            'new_patients_count'   => $newPatients,
            'total_appointments'   => $totalAppointments,
            'completed_treatments' => $completedTreatments,
        ], 'Monthly summary fetched successfully');
    }

    /**
     * GET /api/v1/reports/doctor-commission
     * Doctor commission report for a given month/year
     * Query: ?doctor_id=xxx&month=1&year=2024
     * Note: doctor_id here is the DOCTOR table's id (not user_id)
     */
    public function doctorCommission(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'month'     => 'required|integer|min:1|max:12',
            'year'      => 'required|integer',
        ]);

        $doctor = Doctor::with('user')->find($request->doctor_id);

        $treatments = PatientTreatment::where('doctor_id', $doctor->id)
            ->whereMonth('created_at', $request->month)
            ->whereYear('created_at', $request->year)
            ->get();

        $totalTreatments = $treatments->count();
        $totalRevenue    = $treatments->sum('cost');
        $commissionAmount = ($totalRevenue * ($doctor->commission_percentage ?? 0)) / 100;

        return $this->successResponse([
            'doctor_id'             => $doctor->id,
            'doctor_name'           => $doctor->user?->name,
            'month'                 => (int) $request->month,
            'year'                  => (int) $request->year,
            'total_treatments'      => $totalTreatments,
            'total_revenue'         => (float) $totalRevenue,
            'commission_percentage' => (float) ($doctor->commission_percentage ?? 0),
            'commission_amount'     => (float) $commissionAmount,
        ], 'Doctor commission fetched successfully');
    }

    /**
     * GET /api/v1/reports/patient-summary
     * Patient visits and payment summary
     * Query: ?patient_id=xxx&start_date=2024-01-01&end_date=2024-12-31
     */
    public function patientSummary(Request $request)
    {
        $query = Patient::withCount('appointments')
            ->withSum('payments', 'paid_amount')
            ->withSum('payments', 'balance_amount');

        if ($request->patient_id) {
            $query->where('id', $request->patient_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereHas('appointments', function ($q) use ($request) {
                $q->whereBetween('appointment_date', [$request->start_date, $request->end_date]);
            });
        }

        $patients = $query->get()->map(function ($patient) {
            return [
                'patient_id'      => $patient->id,
                'name'            => $patient->name,
                'total_visits'    => $patient->appointments_count,
                'total_spent'     => (float) ($patient->payments_sum_paid_amount ?? 0),
                'pending_balance' => (float) ($patient->payments_sum_balance_amount ?? 0),
            ];
        });

        return $this->successResponse($patients, 'Patient summary fetched successfully');
    }

    /**
     * GET /api/v1/reports/export/payments
     * Download payments report
     * Query: ?format=pdf (default: xlsx)
     *        &start_date=&end_date=&status=&patient_id=
     */
    public function exportPayments(Request $request)
    {
        if ($request->format === 'pdf') {
            $export   = new PaymentsExport($request);
            $payments = $export->collection();
            $pdf = Pdf::loadView('exports.payments', compact('payments'));
            return $pdf->download('payments_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return Excel::download(new PaymentsExport($request), 'payments_report_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * GET /api/v1/reports/export/appointments
     * Download appointments report
     * Query: ?format=pdf (default: xlsx)
     *        &start_date=&end_date=&status=&doctor_id=
     */
    public function exportAppointments(Request $request)
    {
        if ($request->format === 'pdf') {
            $export       = new AppointmentsExport($request);
            $appointments = $export->collection();
            $pdf = Pdf::loadView('exports.appointments', compact('appointments'));
            return $pdf->download('appointments_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return Excel::download(new AppointmentsExport($request), 'appointments_report_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * GET /api/v1/reports/export/patients
     * Download patients report
     * Query: ?format=pdf (default: xlsx)
     *        &start_date=&end_date=
     */
    public function exportPatients(Request $request)
    {
        if ($request->format === 'pdf') {
            $export   = new PatientsExport($request);
            $patients = $export->collection();
            $pdf = Pdf::loadView('exports.patients', compact('patients'));
            return $pdf->download('patients_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return Excel::download(new PatientsExport($request), 'patients_report_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * GET /api/v1/reports/export/doctors
     * Download doctors report
     * Query: ?format=pdf (default: xlsx)
     */
    public function exportDoctors(Request $request)
    {
        if ($request->format === 'pdf') {
            $export  = new DoctorsExport($request);
            $doctors = $export->collection();
            $pdf = Pdf::loadView('exports.doctors', compact('doctors'));
            return $pdf->download('doctors_report_' . now()->format('Y-m-d') . '.pdf');
        }

        return Excel::download(new DoctorsExport($request), 'doctors_report_' . now()->format('Y-m-d') . '.xlsx');
    }
}
