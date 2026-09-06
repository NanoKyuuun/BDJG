<?php

namespace App\Domains\CRM\Controllers\Public;

use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Domains\CRM\Requests\SubmitPublicInquiryRequest;
use App\Domains\CRM\Resources\InquiryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PublicInquiryController extends Controller
{
    public function __invoke(SubmitPublicInquiryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $addOnIds = $data['add_on_ids'] ?? [];
        unset($data['add_on_ids']);

        $data['status'] = InquiryStatus::New;

        $inquiry = Inquiry::create($data);

        if (! empty($addOnIds)) {
            $inquiry->addOns()->sync($addOnIds);
        }

        return (new InquiryResource($inquiry->load(['service', 'package', 'addOns'])))
            ->response()
            ->setStatusCode(201);
    }
}
