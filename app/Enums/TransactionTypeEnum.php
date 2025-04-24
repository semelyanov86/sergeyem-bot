<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case WITHDRAWAL = 'withdrawal';

    case DEPOSIT = 'deposit';

    case TRANSFER = 'transfer';

    case RECONCILIATION = 'reconciliation';
}
