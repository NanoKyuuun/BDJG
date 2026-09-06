@extends('emails.layout', ['title' => 'Deliverables Ready for Download - BDJG Creative Studio'])

@section('content')
    <h1>Your Final Master Package is Ready</h1>
    <p>Dear <strong>{{ $clientName }}</strong>,</p>
    <p>We are delighted to inform you that the final master deliverables for <strong>{{ $package->project?->name }}</strong> have been rendered, approved, and packaged for high-speed download.</p>

    <div class="highlight-box">
        <p style="margin: 0 0 8px;"><strong>Package Title:</strong> {{ $package->title }}</p>
        <p style="margin: 0 0 8px;"><strong>Total Files:</strong> {{ $package->file_count }} files</p>
        <p style="margin: 0 0 8px;"><strong>Total Package Size:</strong> {{ round($package->total_size_bytes / 1048576, 2) }} MB</p>
        <p style="margin: 0;"><strong>Valid Until:</strong> {{ $package->expires_at ? $package->expires_at->format('d M Y') : '30 days' }}</p>
    </div>

    <p>Click the button below to access your secure high-speed signed download link:</p>

    <div class="btn-container">
        <a href="{{ $downloadUrl }}" class="btn">Download Deliverables</a>
    </div>

    <p style="font-size: 13px; color: #a1a1aa;">
        Thank you for trusting BDJG Creative Studio. It has been a pleasure bringing your vision to life!
    </p>
@endsection
