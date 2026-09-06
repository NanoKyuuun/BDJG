<?php

namespace App\Domains\Media\Controllers;

use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Models\PendingUpload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectStreamController extends Controller
{
    /**
     * Local simulated direct upload handler (PUT endpoint for local disk)
     */
    public function directUpload(Request $request, string $public_id): Response
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired upload signature.');
        }

        $pendingUpload = PendingUpload::where('public_id', $public_id)->firstOrFail();

        $content = $request->getContent();
        Storage::disk($pendingUpload->disk)->put($pendingUpload->storage_key, $content);

        return response('Upload completed', 200);
    }

    /**
     * Stream media file securely via temporary signed URL
     */
    public function stream(Request $request, string $mediaAsset): StreamedResponse|Response
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired media access link.');
        }

        $asset = MediaAsset::where('public_id', $mediaAsset)->firstOrFail();

        if (! Storage::disk($asset->disk)->exists($asset->storage_key)) {
            // Return dummy binary stream for development if file not present
            return response("BDJG Media Binary Stream: {$asset->original_name}", 200, [
                'Content-Type' => $asset->mime_type,
                'Content-Disposition' => "inline; filename=\"{$asset->filename}\"",
            ]);
        }

        return Storage::disk($asset->disk)->response($asset->storage_key, $asset->filename, [
            'Content-Type' => $asset->mime_type,
            'Content-Disposition' => "inline; filename=\"{$asset->filename}\"",
        ]);
    }
}
