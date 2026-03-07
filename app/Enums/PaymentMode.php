<?php

namespace App\Enums;

enum PaymentMode: string
{
    case Check = 'CHECK';
    case Other = 'OTHER';
    case Cash = 'CASH';
    case CreditCard = 'CREDIT_CARD';
    case BankTransfer = 'BANK_TRANSFER';
}
