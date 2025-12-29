<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use Inertia\Inertia;

class FeePaymentController extends Controller
{
    public function index()
    {
        $payments = FeePayment::with(['student', 'fee.grade'])->get();

        return Inertia::render('FeePayments/Index', [
            'payments' => $payments
        ]);
    }
}
