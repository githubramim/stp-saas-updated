<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $transaction->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .receipt-box { max-width: 400px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #666; }
        .details { margin-bottom: 15px; }
        .details table { width: 100%; }
        .details td { padding: 3px 0; }
        .details td:last-child { text-align: right; }
        .amount { font-size: 20px; font-weight: bold; text-align: center; padding: 10px; margin: 10px 0; background: #f3f4f6; }
        .footer { text-align: center; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #888; }
        .signature { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature div { text-align: center; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="header">
            <h2>Payment Receipt</h2>
            <p>{{ $transaction->institute?->name ?? 'STP Training Center' }}</p>
            <p>Receipt No: {{ $transaction->receipt_number }}</p>
        </div>

        <div class="details">
            <table>
                <tr><td>Date:</td><td>{{ $transaction->transaction_date }}</td></tr>
                <tr><td>Student:</td><td>{{ $transaction->student?->name_en ?? '—' }}</td></tr>
                <tr><td>Phone:</td><td>{{ $transaction->student?->contact ?? '—' }}</td></tr>
                <tr><td>Category:</td><td>{{ $transaction->category }}</td></tr>
                <tr><td>Payment Method:</td><td>{{ $transaction->payment_method ?? '—' }}</td></tr>
            </table>
        </div>

        <div class="amount">
            ৳{{ number_format($transaction->amount, 2) }}
        </div>

        @if($transaction->description)
            <p style="font-size:11px; color:#666;"><strong>Note:</strong> {{ $transaction->description }}</p>
        @endif

        <div class="signature">
            <div>
                <p>_________________</p>
                <p>Received By</p>
            </div>
            <div>
                <p>_________________</p>
                <p>Student Signature</p>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt.</p>
            <p>Generated on {{ now()->format('d M Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
