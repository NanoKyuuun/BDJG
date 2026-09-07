<?php

namespace App\Domains\Orders\Enums;

enum ServiceOrderReviewType: string
{
    case AutoCheckout = 'AUTO_CHECKOUT';
    case AdminReview = 'ADMIN_REVIEW';
}
