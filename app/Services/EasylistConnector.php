<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\EasylistItemData;
use App\Data\EasylistListData;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

final class EasylistConnector
{
    /**
     * @return EasylistListData[]
     */
    public function getLists(): array
    {
        /** @var string $url */
        $url = config('services.easylist.url');

        $response = $this->getRequest()->get($url . '/lists');
        if (! $response->ok()) {
            throw new \DomainException('Can not get lists: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return EasylistListData::collect($response->json('data'), 'array');
    }

    /**
     * @return EasylistItemData[]
     */
    public function getListItems(int $listId): array
    {
        /** @var string $url */
        $url = config('services.easylist.url');

        $response = $this->getRequest()->get($url . '/lists/' . $listId . '/items');
        if ($response->status() === Response::HTTP_NOT_FOUND) {
            throw new RecordNotFoundException('Provided list not found');
        }
        if (! $response->ok()) {
            throw new \DomainException('Can not get items: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return EasylistItemData::collect($response->json('data'), 'array');
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.easylist.token');

        return Http::withToken($token)->asJson();
    }
}
