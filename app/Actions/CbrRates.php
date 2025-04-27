<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CbrRateData;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

final class CbrRates
{
    use AsAction;

    /**
     * @return array{date: string, rates: array<string, CbrRateData>}
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function handle(): array
    {
        /** @var string $url */
        $url = config('services.currency.cbr_url');
        $response = Http::get($url);
        if (! $response->ok()) {
            throw new \DomainException('Ошибка при получении курсов валют ЦБ РФ');
        }
        /** @var \SimpleXMLElement $xml */
        $xml = simplexml_load_string($response->body());
        $result = ['date' => (string) $xml['Date'], 'rates' => []];

        foreach ($xml->Valute as $valute) {
            $result['rates'][(string) $valute->CharCode] = new CbrRateData(
                id: (string) $valute['ID'],
                num_code: (string) $valute->NumCode,
                char_code: (string) $valute->CharCode,
                nominal: (int) $valute->Nominal,
                name: (string) $valute->Name,
                value: (float) str_replace(',', '.', (string) $valute->Value),
                vunit_rate: (float) str_replace(',', '.', (string) $valute->VunitRate),
            );
        }

        return $result;
    }
}
