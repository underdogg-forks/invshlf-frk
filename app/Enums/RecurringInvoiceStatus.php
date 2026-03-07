<?php

namespace App\Enums;

enum RecurringInvoiceStatus: string
{
    case Completed = 'COMPLETED';
    case OnHold = 'ON_HOLD';
    case Active = 'ACTIVE';
}
