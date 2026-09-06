@extends('emails.layout', ['title' => 'Quotation #' . $quotation->quotation_number . ' - BDJG Creative Studio'])

@section('content')
    <h1>Quotation Ready for Review</h1>
    <p>Dear <strong>{{ $clientName }}</strong>,</p>
    <p>Our production team has prepared the official quotation for your upcoming project with BDJG Creative Studio.</p>

    <div class="highlight-box">
        <p style="margin: 0 0 8px;"><strong>Quotation Number:</strong> #{{ $quotation->quotation_number }}</p>
        <p style="margin: 0 0 8px;"><strong>Total Amount:</strong> <span style="color: #f59e0b; font-weight: 700; font-size: 16px;">IDR {{ number_format($quotation->total_amount, 0, ',', '.') }}</span></p>
        <p style="margin: 0 0 8px;"><strong>Valid Until:</strong> {{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '14 days' }}</p>
        <p style="margin: 0;"><strong>Status:</strong> Awaiting Your Review & Acceptance</p>
    </div>

    <p>You can review the full itemized scope, terms, and accept or request adjustments directly from your portal:</p>

    <div class="btn-container">
        <a href="{{ $viewUrl }}" class="btn">Review & Accept Quotation</a>
    </div>

    <p style="font-size: 13px; color: #a1a1aa;">
        If you have any questions or require custom adjustments, feel free to submit feedback directly from the quotation review page.
    </p>
@endsection
