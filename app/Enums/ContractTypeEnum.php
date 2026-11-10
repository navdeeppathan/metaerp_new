<?php

namespace App\Enums;

enum ContractTypeEnum: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';
    case BUY_AND_SELL = 'BUY_AND_SELL';
}
