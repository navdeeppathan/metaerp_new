<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'PENDING';
    case PARTIAL = 'PARTIAL';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
}
