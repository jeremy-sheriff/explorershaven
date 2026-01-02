<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\FeeCreditController;
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
Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show'); // MOVED BEFORE PUT/DELETE
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

require __DIR__ . '/settings.php';
