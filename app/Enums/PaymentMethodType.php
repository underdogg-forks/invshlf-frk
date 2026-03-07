<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case General = 'GENERAL';
    case Module = 'MODULE';
}
