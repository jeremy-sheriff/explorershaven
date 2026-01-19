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

@if(count($filters) > 0)
    <div class="filters">
        <strong>Applied Filters:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            @foreach($filters as $filter)
                <li>{{ $filter }}</li>
            @endforeach
        </ul>
    </div>
@endif

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Adm No</th>
        <th>Name</th>
        <th>Grade</th>
        <th>Gender</th>
        <th>Guardian</th>
        <th>Phone</th>
    </tr>
    </thead>
    <tbody>
    @forelse($students as $index => $student)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $student->adm_no }}</td>
            <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
            <td>{{ $student->grade->name ?? 'N/A' }}</td>
            <td>{{ ucfirst($student->gender) }}</td>
            <td>{{ $student->guardian->first_name ?? '' }} {{ $student->guardian->last_name ?? '' }}</td>
            <td>{{ $student->guardian->phone_number ?? 'N/A' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align: center; padding: 20px;">No students found</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    <p>This is a system-generated report. No signature required.</p>
</div>
</body>
</html>
