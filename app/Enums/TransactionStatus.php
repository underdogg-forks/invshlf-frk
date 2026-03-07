<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'PENDING';
    case Failed = 'FAILED';
    case Success = 'SUCCESS';
}
