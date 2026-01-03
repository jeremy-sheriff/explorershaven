<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeeCredit;
use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FeePaymentController extends Controller
{
    public function index(Request $request)
    {
        // Determine current term based on current month
        $currentMonth = now()->month;
        $currentYear = 2026;

        $currentTerm = match(true) {
            $currentMonth >= 1 && $currentMonth <= 4 => "TERM ONE {$currentYear}",
            $currentMonth >= 5 && $currentMonth <= 8 => "TERM TWO {$currentYear}",
            $currentMonth >= 9 && $currentMonth <= 12 => "TERM THREE {$currentYear}",
            default => "TERM ONE {$currentYear}"
        };

        $query = FeePayment::with(['student.grade', 'fee.grade']);

        // Filter by grade (ignore if 'all' or empty)
        if ($request->filled('grade') && $request->grade !== 'all') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->grade);
            });
        }

        // Filter by term (ignore if 'all' or empty)
        if ($request->filled('term') && $request->term !== 'all') {
            $query->whereHas('fee', function ($q) use ($request) {
                $q->where('term', $request->term);
            });
        }

        // Filter by status (ignore if 'all' or empty)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by student (ignore if 'all' or empty)
        if ($request->filled('student') && $request->student !== 'all') {
            $query->where('student_id', $request->student);
        }

        // Filter by payment date range
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        // Filter by balance (ignore if 'all' or empty)
        if ($request->filled('has_balance') && $request->has_balance !== 'all') {
            if ($request->has_balance === 'yes') {
                $query->where('balance', '>', 0);
            } elseif ($request->has_balance === 'no') {
                $query->where('balance', '=', 0);
            }
        }

        // Sort by balance (ignore if 'all' or empty)
        if ($request->filled('sort_balance') && $request->sort_balance !== 'all') {
            $query->orderBy('balance', $request->sort_balance);
        } else {
            $query->latest();
        }

        $payments = $query->get();
        $students = Student::with('grade')->get();

        // Only get fees for the current term
        $fees = Fee::with('grade')
            ->where('term', $currentTerm)
            ->get();

        $grades = Grade::all();

        // Only show current term
        $terms = [$currentTerm];

        // Get available credits for each student with adjusted amounts
        $creditsData = FeeCredit::where('status', 'available')
            ->with(['student', 'appliedToFee'])
            ->get()
            ->groupBy('student_id');

        // Calculate adjusted balances for current term fees considering available credits
        $adjustedBalances = [];
        foreach ($students as $student) {
            $studentId = $student->id;

            // Get available credit for this student
            $availableCredit = $creditsData->get($studentId)?->sum('amount') ?? 0;

            // Get current term fees for this student's grade
            $currentTermFees = $fees->where('grade_id', $student->grade_id);

            foreach ($currentTermFees as $fee) {
                // Calculate total paid for this fee
                $totalPaid = FeePayment::where('student_id', $studentId)
                    ->where('fee_id', $fee->id)
                    ->sum('amount_paid');

                // Calculate raw balance
                $rawBalance = $fee->amount - $totalPaid;

                // Apply available credit to balance
                $adjustedBalance = max(0, $rawBalance - $availableCredit);

                $adjustedBalances[$studentId][$fee->id] = [
                    'raw_balance' => $rawBalance,
                    'adjusted_balance' => $adjustedBalance,
                    'credit_applied' => min($rawBalance, $availableCredit)
                ];
            }
        }

        return Inertia::render('FeePayments/Index', [
            'payments' => $payments,
            'students' => $students,
            'fees' => $fees,
            'grades' => $grades,
            'terms' => $terms,
            'credits' => $creditsData,
            'adjustedBalances' => $adjustedBalances,
            'currentTerm' => $currentTerm,
            'filters' => $request->only(['grade', 'term', 'status', 'student', 'date_from', 'date_to', 'has_balance', 'sort_balance']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_id' => 'required|exists:fees,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            $fee = Fee::findOrFail($validated['fee_id']);
            $studentId = $validated['student_id'];
            $amountPaid = $validated['amount_paid'];

            // Determine current term
            $currentMonth = now()->month;
            $currentTerm = match(true) {
                $currentMonth >= 1 && $currentMonth <= 4 => "TERM ONE {$currentYear}",
                $currentMonth >= 5 && $currentMonth <= 8 => "TERM TWO {$currentYear}",
                $currentMonth >= 9 && $currentMonth <= 12 => "TERM THREE {$currentYear}",
                default => "TERM ONE {$currentYear}"
            };

            // Validate that the fee is for the current term
            if ($fee->term !== $currentTerm) {
                return back()->withErrors([
                    'fee_id' => "Payments can only be made for the current term ({$currentTerm}). This fee is for {$fee->term}."
                ]);
            }

            // Calculate total paid for this fee by this student
            $totalPaidBefore = FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->sum('amount_paid');

            // Check if fee is already fully paid
            if ($totalPaidBefore >= $fee->amount) {
                return back()->withErrors([
                    'fee_id' => 'This fee has already been fully paid. No additional payments can be recorded.'
                ]);
            }

            $totalPaidNow = $totalPaidBefore + $amountPaid;
            $balance = $fee->amount - $totalPaidNow;

            // Auto-calculate status based on balance
            $status = ($balance <= 0) ? 'paid' : 'partial';

            // Create the payment record with the correct balance
            $payment = FeePayment::create([
                'student_id' => $studentId,
                'fee_id' => $validated['fee_id'],
                'amount_paid' => $amountPaid,
                'balance' => max(0, $balance),
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

            // Update status of all previous payments for this fee to match
            FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->where('id', '!=', $payment->id)
                ->update([
                    'status' => $status,
                    'balance' => max(0, $balance)
                ]);
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
        ]);

        DB::transaction(function () use ($validated, $feePayment) {
            $fee = Fee::findOrFail($validated['fee_id']);
            $studentId = $validated['student_id'];
            $amountPaid = $validated['amount_paid'];

            // Determine current term
            $currentMonth = now()->month;
            $currentTerm = match(true) {
                $currentMonth >= 1 && $currentMonth <= 4 => "TERM ONE {$currentYear}",
                $currentMonth >= 5 && $currentMonth <= 8 => "TERM TWO {$currentYear}",
                $currentMonth >= 9 && $currentMonth <= 12 => "TERM THREE {$currentYear}",
                default => "TERM ONE {$currentYear}"
            };

            // Validate that the fee is for the current term
            if ($fee->term !== $currentTerm) {
                return back()->withErrors([
                    'fee_id' => "Payments can only be made for the current term ({$currentTerm}). This fee is for {$fee->term}."
                ]);
            }

            // Calculate total paid for this fee by this student (excluding current payment)
            $totalPaidBefore = FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->where('id', '!=', $feePayment->id)
                ->sum('amount_paid');

            $totalPaidNow = $totalPaidBefore + $amountPaid;
            $balance = $fee->amount - $totalPaidNow;

            // Auto-calculate status based on balance
            $status = ($balance <= 0) ? 'paid' : 'partial';

            // Update the payment record
            $feePayment->update([
                'student_id' => $studentId,
                'fee_id' => $validated['fee_id'],
                'amount_paid' => $amountPaid,
                'balance' => max(0, $balance),
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

            // Update status AND balance of all other payments for this fee
            FeePayment::where('student_id', $studentId)
                ->where('fee_id', $validated['fee_id'])
                ->where('id', '!=', $feePayment->id)
                ->update([
                    'status' => $status,
                    'balance' => max(0, $balance)
                ]);
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

            // Auto-calculate status based on balance
            $status = ($balance <= 0) ? 'paid' : 'partial';

            // Update both status AND balance for all remaining payments
            FeePayment::where('student_id', $studentId)
                ->where('fee_id', $feeId)
                ->update([
                    'status' => $status,
                    'balance' => max(0, $balance)
                ]);
        });

        return redirect()->route('fee-payments.index');
    }
}
