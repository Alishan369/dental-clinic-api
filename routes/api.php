<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PatientHistoryController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\PatientTreatmentController;
use App\Http\Controllers\TreatmentTypeController;

Route::get('/', fn() => response()->json(['message' => 'API Working!']));

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
        });
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('me', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('users', [AdminController::class, 'index']);
            Route::post('users/{id}/accept', [AdminController::class, 'acceptUser']);
            Route::post('users/{id}/reject', [AdminController::class, 'rejectUser']);
            Route::post('users/{id}/toggle', [AdminController::class, 'toggleUserStatus']);
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
            Route::get('/{id}/history', [PatientHistoryController::class, 'index']);
            Route::post('/{id}/history', [PatientHistoryController::class, 'store']);
            Route::get('/{id}/documents', [MedicalDocumentController::class, 'index']);
            Route::post('/{id}/documents', [MedicalDocumentController::class, 'store']);
            Route::get('/{id}/treatments', [PatientTreatmentController::class, 'index']);
            Route::post('/{id}/treatments', [PatientTreatmentController::class, 'store']);
        });

        Route::delete('documents/{id}', [MedicalDocumentController::class, 'destroy']);

        Route::prefix('diseases')->group(function () {
            Route::get('/', [DiseaseController::class, 'index']);
        });

        Route::prefix('treatment-types')->group(function () {
            Route::get('/', [TreatmentTypeController::class, 'index']);
            Route::get('/{id}', [TreatmentTypeController::class, 'show']);
            Route::post('/', [TreatmentTypeController::class, 'store']);
            Route::put('/{id}', [TreatmentTypeController::class, 'update']);
            Route::delete('/{id}', [TreatmentTypeController::class, 'destroy']);
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
            Route::get('/', [PaymentController::class, 'index']);
            // Named routes MUST come before the /{id} wildcard to avoid routing conflicts
            Route::get('/pending', [PaymentController::class, 'getPendingPayments']);
            Route::get('/overdue', [PaymentController::class, 'getOverduePayments']);
            Route::get('/daily-closing', [PaymentController::class, 'getDailyClosing']);
            Route::post('/record', [PaymentController::class, 'recordPayment']);
            Route::post('/installment-plan', [PaymentController::class, 'addInstallmentPlan']);
            // Wildcard route last
            Route::get('/{id}', [PaymentController::class, 'show']);
            Route::post('/', [PaymentController::class, 'store']);
        });

        Route::prefix('installments')->group(function () {
            Route::post('/{id}/pay', [InstallmentController::class, 'pay']);
            Route::get('/overdue', [InstallmentController::class, 'getOverdue']);
            Route::get('/upcoming', [InstallmentController::class, 'getUpcoming']);
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
            Route::get('/daily-collection', [ReportController::class, 'dailyCollection']);
            Route::get('/monthly-summary', [ReportController::class, 'monthlySummary']);
            Route::get('/doctor-commission', [ReportController::class, 'doctorCommission']);
            Route::get('/patient-summary', [ReportController::class, 'patientSummary']);

            // Export (download) reports
            Route::get('/export/payments', [ReportController::class, 'exportPayments']);
            Route::get('/export/appointments', [ReportController::class, 'exportAppointments']);
            Route::get('/export/patients', [ReportController::class, 'exportPatients']);
            Route::get('/export/doctors', [ReportController::class, 'exportDoctors']);
        });

    });
});
