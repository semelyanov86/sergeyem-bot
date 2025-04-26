<?php

declare(strict_types=1);

namespace App\Enums;

enum ChatStateEnum: string
{
    case ACTIVE = 'active';

    case ASK_WORD_ORIGINAL = 'askWordOriginal';

    case ASK_WORD_TRANSLATION = 'askWordTranslation';

    case ASK_WORD_LANGUAGE = 'askWordLanguage';
}
