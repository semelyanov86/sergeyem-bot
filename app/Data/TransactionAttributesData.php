<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

final class TransactionAttributesData extends Data
{
    /**
     * @param  TransactionDetailData[]  $transactions
     */
    public function __construct(
        public Carbon $created_at,
        public Carbon $updated_at,
        public string $user,
        public ?string $group_title,
        public array $transactions,
    ) {}
}
