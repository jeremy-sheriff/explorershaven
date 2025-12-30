<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeeCredit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FeePaymentController extends Controller
{
    public function index()
    {
        $payments = FeePayment::with(['student', 'fee.grade'])->latest()->get();
        $students = Student::with('grade')->get();
        $fees = Fee::with('grade')->get();

        // Get available credits for each student
        $credits = FeeCredit::where('status', 'available')
            ->with(['student', 'appliedToFee'])
            ->get()
            ->groupBy('student_id');

        return Inertia::render('FeePayments/Index', [
            'payments' => $payments,
            'students' => $students,
            'fees' => $fees,
            'credits' => $credits,
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

        DB::transaction(function () use ($validated) {
            $fee = Fee::findOrFail($validated['fee_id']);
            $studentId = $validated['student_id'];
            $amountPaid = $validated['amount_paid'];

            // Calculate total paid for this fee by this student
            $totalPaidBefore = FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->sum('amount_paid');

            $totalPaidNow = $totalPaidBefore + $amountPaid;
            $balance = $fee->amount - $totalPaidNow;

            // Determine status based on balance
            if ($balance <= 0) {
                $status = 'paid';
            } elseif ($totalPaidNow > 0 && $balance > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }

            // Create the payment record
            $payment = FeePayment::create([
                'student_id' => $studentId,
                'fee_id' => $validated['fee_id'],
                'amount_paid' => $amountPaid,
                'balance' => $balance < 0 ? 0 : $balance,
                'payment_date' => $validated['payment_date'],
                'status' => $status,
            ]);

            // If overpayment (balance is negative), create a credit
            if ($balance < 0) {
                $creditAmount = abs($balance);

                FeeCredit::create([
                    'student_id' => $studentId,
                    'from_payment_id' => $payment->id,
                    'applied_to_fee_id' => null,
                    'amount' => $creditAmount,
                    'status' => 'available',
                    'notes' => "Credit from overpayment on {$fee->term}",
                ]);
            }

            // Update status of all previous payments for this fee
            FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->where('id', '!=', $payment->id)
                ->update(['status' => $status]);
        });

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

        DB::transaction(function () use ($validated, $feePayment) {
            $fee = Fee::findOrFail($validated['fee_id']);
            $studentId = $validated['student_id'];
            $amountPaid = $validated['amount_paid'];

            // Calculate total paid for this fee by this student (excluding current payment)
            $totalPaidBefore = FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->where('id', '!=', $feePayment->id)
                ->sum('amount_paid');

            $totalPaidNow = $totalPaidBefore + $amountPaid;
            $balance = $fee->amount - $totalPaidNow;

            // Determine status based on balance
            if ($balance <= 0) {
                $status = 'paid';
            } elseif ($totalPaidNow > 0 && $balance > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }

            // Update the payment record
            $feePayment->update([
                'student_id' => $studentId,
                'fee_id' => $validated['fee_id'],
                'amount_paid' => $amountPaid,
                'balance' => $balance < 0 ? 0 : $balance,
                'payment_date' => $validated['payment_date'],
                'status' => $status,
            ]);

            // Delete old credits from this payment
            FeeCredit::where('from_payment_id', $feePayment->id)->delete();

            // If overpayment (balance is negative), create a credit
            if ($balance < 0) {
                $creditAmount = abs($balance);

                FeeCredit::create([
                    'student_id' => $studentId,
                    'from_payment_id' => $feePayment->id,
                    'applied_to_fee_id' => null,
                    'amount' => $creditAmount,
                    'status' => 'available',
                    'notes' => "Credit from overpayment on {$fee->term}",
                ]);
            }

            // Update status of all other payments for this fee
            FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->where('id', '!=', $feePayment->id)
                ->update(['status' => $status]);
        });

        return redirect()->route('fee-payments.index');
    }

    public function destroy(FeePayment $feePayment)
    {
        DB::transaction(function () use ($feePayment) {
            $studentId = $feePayment->student_id;
            $feeId = $feePayment->fee_id;

            // Delete any credits created from this payment
            FeeCredit::where('from_payment_id', $feePayment->id)->delete();

            // Delete the payment
            $feePayment->delete();

            // Recalculate status for remaining payments
            $fee = Fee::findOrFail($feeId);
            $totalPaid = FeePayment::where('student_id', $studentId)
                ->where('fee_id', $feeId)
                ->sum('amount_paid');

            $balance = $fee->amount - $totalPaid;

            if ($balance <= 0) {
                $status = 'paid';
            } elseif ($totalPaid > 0 && $balance > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }

            FeePayment::where('student_id', $studentId)
                ->where('fee_id', $feeId)
                ->update(['status' => $status]);
        });

        return redirect()->route('fee-payments.index');
    }
}
