<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\FeeCreditController;
use App\Http\Controllers\StudentProgressionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return redirect('/login');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Students routes - IMPORTANT: GET routes must come before PUT/DELETE routes
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

// Fee routes
Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
Route::put('/fees/{fee}', [FeeController::class, 'update'])->name('fees.update');
Route::delete('/fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');

Route::get('/fee-payments', [FeePaymentController::class, 'index'])->name('fee-payments.index');
Route::post('/fee-payments', [FeePaymentController::class, 'store'])->name('fee-payments.store');
Route::put('/fee-payments/{feePayment}', [FeePaymentController::class, 'update'])->name('fee-payments.update');
Route::delete('/fee-payments/{feePayment}', [FeePaymentController::class, 'destroy'])->name('fee-payments.destroy');

Route::get('/fee-credits', [FeeCreditController::class, 'index'])->name('fee-credits.index');

// Student Progression routes
Route::get('/student-progression', [StudentProgressionController::class, 'index'])->name('student-progression.index');
Route::get('/student-progression/history', [StudentProgressionController::class, 'history'])->name('student-progression.history');
Route::post('/student-progression/promote-student/{student}', [StudentProgressionController::class, 'promoteStudent'])->name('student-progression.promote-student');
Route::post('/student-progression/promote-grade', [StudentProgressionController::class, 'promoteGrade'])->name('student-progression.promote-grade');
Route::post('/student-progression/promote-all', [StudentProgressionController::class, 'promoteAll'])->name('student-progression.promote-all');
Route::post('/student-progression/demote-student/{student}', [StudentProgressionController::class, 'demoteStudent'])->name('student-progression.demote-student');
Route::post('/student-progression/demote-grade', [StudentProgressionController::class, 'demoteGrade'])->name('student-progression.demote-grade');
Route::post('/student-progression/start-new-year', [StudentProgressionController::class, 'startNewYear'])->name('student-progression.start-new-year');
Route::post('/student-progression/graduate-student/{student}', [StudentProgressionController::class, 'graduateStudent'])->name('student-progression.graduate-student');
Route::put('/student-progression/settings', [StudentProgressionController::class, 'updateSettings'])->name('student-progression.update-settings');

require __DIR__ . '/settings.php';
