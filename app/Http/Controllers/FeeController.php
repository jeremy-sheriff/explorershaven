<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Inertia\Inertia;

class FeeController extends Controller
{
    public function index()
    {
        $fees = Fee::with('grade')->get();

        return Inertia::render('Fees/Index', [
            'fees' => $fees
        ]);
    }
}
