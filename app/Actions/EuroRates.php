<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

final class EuroRates
{
    use AsAction;

    /**
     * @return array{date: string, base: string, rates: array<string, float>}
     */
    public function handle(): array
    {
        /** @var string $url */
        $url = config('services.currency.eur_url');
        $response = Http::acceptJson()->asJson()->get($url, ['base' => 'EUR', 'access_key' => config('services.currency.eur_key')]);
        if (! $response->ok()) {
            throw new \DomainException('Can not get eur currency');
        }

        // @phpstan-ignore-next-line
        return $response->json();
    }
}
