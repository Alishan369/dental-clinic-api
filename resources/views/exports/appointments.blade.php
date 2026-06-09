<!DOCTYPE html>
<html>
<head>
    <title>Appointments Export</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Appointments Report</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td>{{ $appointment->appointment_date->format('Y-m-d') }}</td>
                <td>{{ $appointment->formatted_time }}</td>
                <td>{{ $appointment->patient->name ?? 'N/A' }}</td>
                <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($appointment->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
