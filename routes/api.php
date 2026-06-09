<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;

Route::get('/', fn() => response()->json(['message' => 'API Working!']));

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('me', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::post('users/{id}/accept', [AdminController::class, 'acceptUser']);
            Route::post('users/{id}/reject', [AdminController::class, 'rejectUser']);
        });

        /*
         |----------------------------------------------------------------------
         | DOCTORS
         | Uses user_id (from users table) as the primary identifier.
         | Doctor profile (specialization, license, etc.) is in doctors table.
         |----------------------------------------------------------------------
         */
        Route::prefix('doctors')->group(function () {
            Route::get('/', [DoctorController::class, 'index']);
            Route::post('/', [DoctorController::class, 'store']);
            Route::get('/{id}', [DoctorController::class, 'show']);
            Route::put('/{id}', [DoctorController::class, 'update']);
            Route::delete('/{id}', [DoctorController::class, 'destroy']);
            Route::get('/{id}/appointments', [DoctorController::class, 'appointments']);

            // Schedule routes — {id} = user_id
            Route::get('/{id}/schedule', [DoctorController::class, 'getSchedule']);
            Route::post('/{id}/schedule', [DoctorController::class, 'storeSchedule']);
            Route::put('/{id}/schedule/{scheduleId}', [DoctorController::class, 'updateSchedule']);
            Route::delete('/{id}/schedule/{scheduleId}', [DoctorController::class, 'deleteSchedule']);
        });

        Route::prefix('patients')->group(function () {
            Route::get('/', [PatientController::class, 'index']);
            Route::post('/', [PatientController::class, 'store']);
            Route::get('/{id}', [PatientController::class, 'show']);
            Route::put('/{id}', [PatientController::class, 'update']);
            Route::delete('/{id}', [PatientController::class, 'destroy']);
            Route::get('/{id}/appointments', [PatientController::class, 'appointments']);
            Route::get('/{id}/history', [\App\Http\Controllers\PatientHistoryController::class, 'index']);
            Route::post('/{id}/history', [\App\Http\Controllers\PatientHistoryController::class, 'store']);
            Route::get('/{id}/documents', [\App\Http\Controllers\MedicalDocumentController::class, 'index']);
            Route::post('/{id}/documents', [\App\Http\Controllers\MedicalDocumentController::class, 'store']);
            Route::get('/{id}/treatments', [\App\Http\Controllers\PatientTreatmentController::class, 'index']);
            Route::post('/{id}/treatments', [\App\Http\Controllers\PatientTreatmentController::class, 'store']);
        });

        Route::delete('documents/{id}', [\App\Http\Controllers\MedicalDocumentController::class, 'destroy']);

        Route::prefix('diseases')->group(function () {
            Route::get('/', [DiseaseController::class, 'index']);
        });

        Route::prefix('treatment-types')->group(function () {
            Route::get('/', [\App\Http\Controllers\TreatmentTypeController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\TreatmentTypeController::class, 'show']);
            Route::post('/', [\App\Http\Controllers\TreatmentTypeController::class, 'store']);
            Route::put('/{id}', [\App\Http\Controllers\TreatmentTypeController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\TreatmentTypeController::class, 'destroy']);
        });

        Route::prefix('appointments')->group(function () {
            Route::get('/today', [AppointmentController::class, 'getToday']);
            Route::get('/', [AppointmentController::class, 'index']);
            Route::post('/', [AppointmentController::class, 'store']);
            Route::get('/{id}', [AppointmentController::class, 'show']);
            Route::put('/{id}', [AppointmentController::class, 'update']);
            Route::post('/{id}/cancel', [AppointmentController::class, 'cancel']);
            Route::delete('/{id}', [AppointmentController::class, 'destroy']);
        });

        Route::prefix('payments')->group(function () {
            Route::get('/', [\App\Http\Controllers\PaymentController::class, 'index']);
            // Named routes MUST come before the /{id} wildcard to avoid routing conflicts
            Route::get('/pending', [\App\Http\Controllers\PaymentController::class, 'getPendingPayments']);
            Route::get('/overdue', [\App\Http\Controllers\PaymentController::class, 'getOverduePayments']);
            Route::get('/daily-closing', [\App\Http\Controllers\PaymentController::class, 'getDailyClosing']);
            Route::post('/record', [\App\Http\Controllers\PaymentController::class, 'recordPayment']);
            Route::post('/installment-plan', [\App\Http\Controllers\PaymentController::class, 'addInstallmentPlan']);
            // Wildcard route last
            Route::get('/{id}', [\App\Http\Controllers\PaymentController::class, 'show']);
            Route::post('/', [\App\Http\Controllers\PaymentController::class, 'store']);
        });

        Route::prefix('installments')->group(function () {
            Route::post('/{id}/pay', [\App\Http\Controllers\InstallmentController::class, 'pay']);
            Route::get('/overdue', [\App\Http\Controllers\InstallmentController::class, 'getOverdue']);
            Route::get('/upcoming', [\App\Http\Controllers\InstallmentController::class, 'getUpcoming']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
        });

        /*
         |----------------------------------------------------------------------
         | REPORTS — Download reports as Excel (.xlsx) or PDF
         | Query Params:
         |   ?format=pdf  → downloads PDF
         |   (default)    → downloads Excel
         |   ?start_date=2024-01-01&end_date=2024-12-31  → date filter
         |   ?status=paid  → status filter (payments)
         |   ?doctor_id=xxx → doctor filter (appointments)
         |   ?patient_id=xxx → patient filter (payments)
         |----------------------------------------------------------------------
         */
        Route::prefix('reports')->group(function () {
            // Data reports (JSON)
            Route::get('/daily-collection', [\App\Http\Controllers\ReportController::class, 'dailyCollection']);
            Route::get('/monthly-summary', [\App\Http\Controllers\ReportController::class, 'monthlySummary']);
            Route::get('/doctor-commission', [\App\Http\Controllers\ReportController::class, 'doctorCommission']);
            Route::get('/patient-summary', [\App\Http\Controllers\ReportController::class, 'patientSummary']);

            // Export (download) reports
            Route::get('/export/payments', [\App\Http\Controllers\ReportController::class, 'exportPayments']);
            Route::get('/export/appointments', [\App\Http\Controllers\ReportController::class, 'exportAppointments']);
            Route::get('/export/patients', [\App\Http\Controllers\ReportController::class, 'exportPatients']);
            Route::get('/export/doctors', [\App\Http\Controllers\ReportController::class, 'exportDoctors']);
        });

    });
});
