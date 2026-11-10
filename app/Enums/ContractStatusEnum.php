<?php

namespace App\Enums;

enum ContractStatusEnum: string
{
    case ACTIVE = 'ACTIVE';
    case CLOSED = 'CLOSED';
    case CANCELLED = 'CANCELLED';
    case REACTIVATED = 'REACTIVATED';
}
