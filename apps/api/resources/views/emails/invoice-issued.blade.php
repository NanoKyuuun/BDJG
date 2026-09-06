@extends('emails.layout', ['title' => 'Invoice #' . $invoice->invoice_number . ' - BDJG Creative Studio'])

@section('content')
    <h1>Invoice Ready for Payment</h1>
    <p>Dear <strong>{{ $clientName }}</strong>,</p>
    <p>Invoice <strong>#{{ $invoice->invoice_number }}</strong> has been officially issued for your project.</p>

    <div class="highlight-box">
        <p style="margin: 0 0 8px;"><strong>Invoice Number:</strong> #{{ $invoice->invoice_number }}</p>
        <p style="margin: 0 0 8px;"><strong>Invoice Type:</strong> {{ $invoice->type->value }}</p>
        <p style="margin: 0 0 8px;"><strong>Amount Due:</strong> <span style="color: #f59e0b; font-weight: 700; font-size: 16px;">IDR {{ number_format($invoice->amount, 0, ',', '.') }}</span></p>
        <p style="margin: 0;"><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Immediate' }}</p>
    </div>

    <p>You can complete payment instantly via QRIS, Virtual Account (BCA, Mandiri, BNI, BRI), or Credit Card through our secure Duitku payment gateway:</p>

    <div class="btn-container">
        <a href="{{ $paymentUrl }}" class="btn">Pay Invoice Online</a>
    </div>

    <p style="font-size: 13px; color: #a1a1aa;">
        Once payment is verified, your project will be instantly activated and our production team notified.
    </p>
@endsection
