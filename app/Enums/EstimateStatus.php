<?php

namespace App\Enums;

enum EstimateStatus: string
{
    case Draft = 'DRAFT';
    case Sent = 'SENT';
    case Viewed = 'VIEWED';
    case Expired = 'EXPIRED';
    case Accepted = 'ACCEPTED';
    case Rejected = 'REJECTED';
}
