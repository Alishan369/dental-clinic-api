<!DOCTYPE html>
<html>
<head>
    <title>Payments Export</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Payments Report</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Received By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                <td>{{ $payment->patient->name ?? 'N/A' }}</td>
                <td>{{ number_format($payment->final_amount, 2) }}</td>
                <td>{{ ucfirst($payment->payment_method) }}</td>
                <td>{{ ucfirst($payment->status) }}</td>
                <td>{{ $payment->treatment?->doctor?->user?->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
