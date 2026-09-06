<?php

namespace App\Domains\CRM\Enums;

enum InquirySource: string
{
    case Website = 'WEBSITE';
    case WhatsApp = 'WHATSAPP';
    case Instagram = 'INSTAGRAM';
    case Referral = 'REFERRAL';
    case Other = 'OTHER';
}
