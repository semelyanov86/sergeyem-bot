<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WebsiteCheckerData;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static WebsiteCheckerData run(string $link, string $keywoard)
 */
final class WebsiteChecker
{
    use AsAction;

    public function handle(string $link, string $keywoard): WebsiteCheckerData
    {
        $startTime = microtime(true);
        $status = 0;
        $keywordFound = false;

        try {
            $response = Http::timeout(10)->get($link);
            $status = $response->status();

            if ($response->successful()) {
                $content = $response->body();
                $keywordFound = str_contains($content, $keywoard);
            }
        } catch (ConnectionException) {
            $status = 0;
        } catch (RequestException $e) {
            $status = $e->response->status();
        } catch (Exception) {
            $status = 0;
        }

        $endTime = microtime(true);
        $speed = round($endTime - $startTime, 4);

        return new WebsiteCheckerData(
            website: $link,
            status: $status,
            keyword_found: $keywordFound,
            speed: $speed
        );
    }
}
