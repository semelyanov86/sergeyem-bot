<?php

declare(strict_types=1);

namespace App\Enums;

enum ChatStateEnum: string
{
    case ACTIVE = 'active';

    case ASK_WORD_ORIGINAL = 'askWordOriginal';

    case ASK_WORD_TRANSLATION = 'askWordTranslation';

    case ASK_WORD_LANGUAGE = 'askWordLanguage';

    case ASK_TRANSACTION_DESCRIPTION = 'askTransactionDescription';

    case ASK_TRANSACTION_AMOUNT = 'askTransactionAmount';

    case ASK_TRANSACTION_ACCOUNT = 'askTransactionAccount';

    case ASK_TRANSACTION_RECIPIENT = 'askTransactionRecipient';

    case ASK_TRANSACTION_CATEGORY = 'askTransactionCategory';

    case ASK_TRANSACTION_BUDGET = 'askTransactionBudget';
}
