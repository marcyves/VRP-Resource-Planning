<?php

namespace App\Enums;

enum ElectronicInvoiceStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Transmitted = 'transmitted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('messages.electronic_invoice_status_draft'),
            self::Ready => __('messages.electronic_invoice_status_ready'),
            self::Transmitted => __('messages.electronic_invoice_status_transmitted'),
            self::Accepted => __('messages.electronic_invoice_status_accepted'),
            self::Rejected => __('messages.electronic_invoice_status_rejected'),
        };
    }
}
