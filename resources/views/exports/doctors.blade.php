<!DOCTYPE html>
<html>
<head>
    <title>Doctors Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { color: #333; }
        p { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #4a90d9; color: white; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h2>Doctors Report</h2>
    <p>Generated on: {{ now()->format('d M Y, H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Specialization</th>
                <th>Experience</th>
                <th>License No.</th>
                <th>Commission %</th>
                <th>Status</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            @foreach($doctors as $index => $doctor)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $doctor->name }}</td>
                <td>{{ $doctor->email }}</td>
                <td>{{ $doctor->phone ?? 'N/A' }}</td>
                <td>{{ $doctor->doctor?->specialization ?? 'N/A' }}</td>
                <td>{{ $doctor->doctor?->experience_years ?? 0 }} yrs</td>
                <td>{{ $doctor->doctor?->license_number ?? 'N/A' }}</td>
                <td>{{ $doctor->doctor?->commission_percentage ?? 0 }}%</td>
                <td>{{ ucfirst($doctor->status) }}</td>
                <td>{{ $doctor->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:15px;">Total Doctors: {{ $doctors->count() }}</p>
</body>
</html>
