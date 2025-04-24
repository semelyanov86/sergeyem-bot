<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TransactionTypeEnum;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

final class FireflyTransactionCreateData extends Data
{
    public function __construct(
        public TransactionTypeEnum $type,
        public float $amount,
        public string $description,
        public int $category_id,
        public int $source_id,
        public int $destination_id,
        public int $budget_id,
        public Carbon $date = new Carbon(),
    ) {}
}
