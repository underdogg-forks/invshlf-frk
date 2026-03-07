<?php

namespace App\Enums;

enum RecurringInvoiceLimitBy: string
{
    case None = 'NONE';
    case Count = 'COUNT';
    case Date = 'DATE';
}
