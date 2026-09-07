<?php

namespace App\Domains\Orders\Enums;

enum OrderAttachmentType: string
{
    case Moodboard = 'MOODBOARD';
    case Rundown = 'RUNDOWN';
    case VenueLayout = 'VENUE_LAYOUT';
    case ContractSample = 'CONTRACT_SAMPLE';
    case Other = 'OTHER';
}
