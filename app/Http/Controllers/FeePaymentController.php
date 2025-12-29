<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Fee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeePaymentController extends Controller
{
    public function index()
    {
        $payments = FeePayment::with(['student', 'fee.grade'])->latest()->get();
        $students = Student::with('grade')->get();
        $fees = Fee::with('grade')->get();

        return Inertia::render('FeePayments/Index', [
            'payments' => $payments,
            'students' => $students,
            'fees' => $fees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_id' => 'required|exists:fees,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,partial,paid',
        ]);

        FeePayment::create($validated);

        return redirect()->route('fee-payments.index');
    }

    public function update(Request $request, FeePayment $feePayment)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_id' => 'required|exists:fees,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,partial,paid',
        ]);

        $feePayment->update($validated);

        return redirect()->route('fee-payments.index');
    }

    public function destroy(FeePayment $feePayment)
    {
        $feePayment->delete();

        return redirect()->route('fee-payments.index');
    }
}
