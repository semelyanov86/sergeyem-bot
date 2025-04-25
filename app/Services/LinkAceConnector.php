<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\LinkaceListData;
use App\Data\LinkData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class LinkAceConnector
{
    /**
     * @return LinkData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getAllLinks(int $number = 6): array
    {
        /** @var string $url */
        $url = config('services.linkace.url');

        $response = $this->getRequest()->get($url . '/links', ['per_page' => $number]);
        if (! $response->ok()) {
            throw new \DomainException('Can not get links: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return LinkData::collect($response->json()['data'], 'array');
    }

    public function createLink(string $urlData): LinkData
    {
        /** @var string $url */
        $url = config('services.linkace.url');
        $response = $this->getRequest()->post($url . '/links', ['url' => $urlData]);
        if ($response->clientError() || $response->serverError()) {
            throw new \DomainException('Can not get links: ' . $response->body());
        }

        return LinkData::from($response->json());
    }

    /**
     * @return LinkaceListData[]
     */
    public function getLists(): array
    {
        /** @var string $url */
        $url = config('services.linkace.url');

        $response = $this->getRequest()->get($url . '/lists');
        if (! $response->ok()) {
            throw new \DomainException('Can not get lists: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return LinkaceListData::collect($response->json()['data'], 'array');
    }

    /**
     * @return LinkData[]
     */
    public function getLinksFromList(int $listId, int $number = 8): array
    {
        /** @var string $url */
        $url = config('services.linkace.url');

        $response = $this->getRequest()->get($url . '/lists/' . $listId . '/links', ['per_page' => $number]);
        if (! $response->ok()) {
            throw new \DomainException('Can not get links from list id ' . $listId . ': ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return LinkData::collect($response->json()['data'], 'array');
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.linkace.token');

        return Http::withToken($token)->acceptJson()->asJson();
    }
}
