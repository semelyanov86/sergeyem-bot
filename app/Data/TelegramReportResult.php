<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

final class TelegramReportResult extends Data
{
    public function __construct(
        public int $sent,
        public int $failed,
        public int $total,
    ) {}

    /**
     * Everything delivered is a plain success, a total loss is a server error,
     * and anything in between is reported as a partial delivery.
     */
    public function httpStatus(): int
    {
        if ($this->failed === 0) {
            return Response::HTTP_OK;
        }

        return $this->sent === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : Response::HTTP_MULTI_STATUS;
    }
}
