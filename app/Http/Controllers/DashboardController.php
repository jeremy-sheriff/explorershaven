<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Fee;
use App\Models\FeePayment;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Total counts
        $totalStudents = Student::count();
        $totalGrades = Grade::count();
        $totalFees = Fee::sum('amount');
        $totalCollected = FeePayment::sum('amount_paid');

        // Students by grade
        $studentsByGrade = Student::with('grade')
            ->get()
            ->groupBy('grade.name')
            ->map(fn($students) => $students->count())
            ->toArray();

        // Payment status breakdown
        $paymentStatus = FeePayment::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent payments
        $recentPayments = FeePayment::with(['student', 'fee'])
            ->latest()
            ->take(5)
            ->get();

        // Monthly collection (last 6 months)
        $monthlyCollection = FeePayment::query()
        ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount_paid) as total')
            ->whereNotNull('payment_date')
            ->where('payment_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalGrades' => $totalGrades,
                'totalFees' => $totalFees,
                'totalCollected' => $totalCollected,
            ],
            'studentsByGrade' => $studentsByGrade,
            'paymentStatus' => $paymentStatus,
            'recentPayments' => $recentPayments,
            'monthlyCollection' => $monthlyCollection,
        ]);
    }
}
