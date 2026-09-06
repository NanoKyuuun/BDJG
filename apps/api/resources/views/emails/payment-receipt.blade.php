@extends('emails.layout', ['title' => 'Payment Receipt - BDJG Creative Studio'])

@section('content')
    <h1>Payment Verified Successfully</h1>
    <p>Dear <strong>{{ $clientName }}</strong>,</p>
    <p>We have successfully received and verified your payment. Thank you!</p>

    <div class="highlight-box">
        <p style="margin: 0 0 8px;"><strong>Transaction Reference:</strong> #{{ $transaction->transaction_reference }}</p>
        <p style="margin: 0 0 8px;"><strong>Invoice:</strong> #{{ $transaction->invoice?->invoice_number }}</p>
        <p style="margin: 0 0 8px;"><strong>Amount Paid:</strong> <span style="color: #10b981; font-weight: 700; font-size: 16px;">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</span></p>
        <p style="margin: 0 0 8px;"><strong>Payment Method:</strong> {{ $transaction->payment_method }}</p>
        <p style="margin: 0;"><strong>Paid At:</strong> {{ $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i') : now()->format('d M Y H:i') }}</p>
    </div>

    <p>Your project schedule and creative team are now active. You can track progress and milestones on your client portal:</p>

    <div class="btn-container">
        <a href="{{ $portalUrl }}" class="btn">View Project Dashboard</a>
    </div>
@endsection
