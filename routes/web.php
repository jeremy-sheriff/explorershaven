<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\StudentController;

//Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canRegister' => Features::enabled(Features::registration()),
//    ]);
//})->name('home');

Route::get('/',function (){
    return redirect('/login');
})->name('home');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
Route::get('/fee-payments', [FeePaymentController::class, 'index'])->name('fee-payments.index');

Route::post('/fee-payments', [FeePaymentController::class, 'store'])->name('fee-payments.store');
Route::put('/fee-payments/{feePayment}', [FeePaymentController::class, 'update'])->name('fee-payments.update');
Route::delete('/fee-payments/{feePayment}', [FeePaymentController::class, 'destroy'])->name('fee-payments.destroy');





require __DIR__.'/settings.php';
