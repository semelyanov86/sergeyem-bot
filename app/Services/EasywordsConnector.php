<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\CreateWordData;
use App\Data\WordData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class EasywordsConnector
{
    /**
     * @return WordData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getRandomWords(int $number = 5): array
    {
        /** @var string $url */
        $url = config('services.easywords.url');

        $response = $this->getRequest()->get($url . '/random/' . $number);
        if (! $response->ok()) {
            throw new \DomainException('Can not get random words: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return WordData::collect($response->json('data'), 'array');
    }

    public function saveWord(CreateWordData $word): WordData
    {
        /** @var string $url */
        $url = config('services.easywords.url');

        $response = $this->getRequest()->post($url . '/words', $word->toArray());
        ray($response->json(), $word->toArray(), $url . '/words');
        if ($response->clientError() || $response->serverError()) {
            throw new \DomainException('Can not save word: ' . $response->body());
        }

        return WordData::from($response->json('data'));
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.easywords.token');

        return Http::withToken($token)->asJson()->acceptJson();
    }
}
