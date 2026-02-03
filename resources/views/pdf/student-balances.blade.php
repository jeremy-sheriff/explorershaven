<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .metadata {
            margin-bottom: 20px;
            font-size: 11px;
        }
        .filters {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .balance-negative {
            color: #dc2626;
            font-weight: bold;
        }
        .balance-zero {
            color: #16a34a;
        }
        .totals-row {
            background-color: #e5e5e5 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
</div>

<div class="metadata">
    <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    <p><strong>Total Students:</strong> {{ $totalStudents }}</p>
</div>

<div class="filters">
    <strong>Filters Applied:</strong>
    Year: {{ $filters['year'] ?? 'All' }} |
    Term: {{ $filters['term'] ?? 'All' }} |
    Grade: {{ $filters['grade'] ?? 'All' }}
</div>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Adm No</th>
        <th>Name</th>
        <th>Grade</th>
        <th class="text-right">Total Fees</th>
        <th class="text-right">Paid</th>
        <th class="text-right">Balance</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalFees = 0;
        $totalPaid = 0;
        $totalBalance = 0;
    @endphp
    @forelse($students as $index => $student)
        @php
            $totalFees += $student->total_fees;
            $totalPaid += $student->total_paid;
            $totalBalance += $student->balance;
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $student->adm_no }}</td>
            <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
            <td>{{ $student->grade->name ?? 'N/A' }}</td>
            <td class="text-right">{{ number_format($student->total_fees, 2) }}</td>
            <td class="text-right">{{ number_format($student->total_paid, 2) }}</td>
            <td class="text-right {{ $student->balance > 0 ? 'balance-negative' : 'balance-zero' }}">
                {{ number_format($student->balance, 2) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align: center; padding: 20px;">No students found</td>
        </tr>
    @endforelse
    <tr class="totals-row">
        <td colspan="4">TOTALS</td>
        <td class="text-right">{{ number_format($totalFees, 2) }}</td>
        <td class="text-right">{{ number_format($totalPaid, 2) }}</td>
        <td class="text-right">{{ number_format($totalBalance, 2) }}</td>
    </tr>
    </tbody>
</table>

<div class="footer">
    <p>This is a system-generated report. No signature required.</p>
</div>
</body>
</html>