<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'DRAFT';
    case Sent = 'SENT';
    case Viewed = 'VIEWED';
    case Completed = 'COMPLETED';
    case Unpaid = 'UNPAID';
    case PartiallyPaid = 'PARTIALLY_PAID';
    case Paid = 'PAID';
}
