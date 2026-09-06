@extends('emails.layout', ['title' => 'Activate Your BDJG Studio Client Account'])

@section('content')
    <h1>Welcome to BDJG Studio</h1>
    <p>Hello <strong>{{ $clientName }}</strong>,</p>
    <p>You have been invited to collaborate with <strong>BDJG Creative Studio</strong>. Your dedicated Client Portal has been provisioned, giving you full real-time access to:</p>
    
    <div class="highlight-box">
        <ul style="margin: 0; padding-left: 20px; color: #e4e4e7;">
            <li>Live project timelines & production schedule</li>
            <li>Interactive 4K video preview & frame-accurate revision tools</li>
            <li>Commercial quotations, invoices & payment history</li>
            <li>High-speed master deliverable downloads</li>
        </ul>
    </div>

    <p>To activate your account and set your secure password, please click the button below:</p>

    <div class="btn-container">
        <a href="{{ $activationUrl }}" class="btn">Activate Client Account</a>
    </div>

    <p style="font-size: 13px; color: #a1a1aa;">
        This invitation link is valid for 7 days. If you did not request this account, please disregard this email.
    </p>
@endsection
