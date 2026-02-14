<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\CreateTickTickTaskData;
use App\Data\TickTickTaskData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class TickTickConnector
{
    /**
     * @return TickTickTaskData[]
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getTasks(): array
    {
        /** @var string $url */
        $url = config('services.ticktick.url');

        $response = $this->getRequest()->get($url . '/project/inbox/data');
        if (! $response->ok()) {
            throw new \DomainException('Can not get tasks: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return TickTickTaskData::collect($response->json('tasks'), 'array');
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function createTask(CreateTickTickTaskData $task): TickTickTaskData
    {
        /** @var string $url */
        $url = config('services.ticktick.url');

        $response = $this->getRequest()->post($url . '/task', $task->toArray());
        if (! $response->ok()) {
            throw new \DomainException('Can not create task: ' . $response->body());
        }

        return TickTickTaskData::from($response->json());
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function completeTask(string $taskId): void
    {
        /** @var string $url */
        $url = config('services.ticktick.url');

        $response = $this->getRequest()->post($url . '/project/inbox/task/' . $taskId . '/complete');
        if (! $response->ok()) {
            throw new \DomainException('Can not complete task: ' . $response->body());
        }
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.ticktick.token');

        return Http::withToken($token)->asJson()->acceptJson();
    }
}
