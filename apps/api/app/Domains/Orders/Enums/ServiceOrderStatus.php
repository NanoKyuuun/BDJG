<?php

namespace App\Domains\Orders\Enums;

enum ServiceOrderStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case NeedsInformation = 'NEEDS_INFORMATION';
    case UnderReview = 'UNDER_REVIEW';
    case QuotationReady = 'QUOTATION_READY';
    case RevisionRequested = 'REVISION_REQUESTED';
    case AwaitingAcceptance = 'AWAITING_ACCEPTANCE';
    case AwaitingPayment = 'AWAITING_PAYMENT';
    case PaymentPending = 'PAYMENT_PENDING';
    case Paid = 'PAID';
    case ProjectCreated = 'PROJECT_CREATED';
    case Declined = 'DECLINED';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Pesanan',
            self::Submitted => 'Menunggu Review',
            self::NeedsInformation => 'Butuh Informasi Tambahan',
            self::UnderReview => 'Sedang Diperiksa',
            self::QuotationReady => 'Penawaran Tersedia',
            self::RevisionRequested => 'Revisi Penawaran Diminta',
            self::AwaitingAcceptance => 'Menunggu Persetujuan',
            self::AwaitingPayment => 'Menunggu Pembayaran DP',
            self::PaymentPending => 'Pembayaran Diproses',
            self::Paid => 'DP Terbayar',
            self::ProjectCreated => 'Proyek Aktif',
            self::Declined => 'Penawaran Ditolak',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
