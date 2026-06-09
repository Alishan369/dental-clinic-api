<!DOCTYPE html>
<html>
<head>
    <title>Patients Export</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Patients Report</h2>
    <table>
        <thead>
            <tr>
                <th>Patient Code</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patients as $patient)
            <tr>
                <td>{{ $patient->patient_code }}</td>
                <td>{{ $patient->name }}</td>
                <td>{{ $patient->phone }}</td>
                <td>{{ $patient->email }}</td>
                <td>{{ ucfirst($patient->gender) }}</td>
                <td>{{ $patient->dob ? $patient->dob->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $patient->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
