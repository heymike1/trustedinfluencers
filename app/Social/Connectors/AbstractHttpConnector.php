<?php

namespace App\Social\Connectors;

use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Exceptions\ConnectorException;
use App\Social\Exceptions\ReconnectionRequiredException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class AbstractHttpConnector implements SocialPlatformConnector
{
    protected function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(20)->retry(2, 500, throw: false);
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return config("social.platforms.{$this->platform()->value}.{$key}", $default);
    }

    /**
     * Turn a failed response into the right exception. 401/403 with an auth error means the
     * creator has to reconnect; anything else is a transient/sync failure.
     */
    protected function ensureOk(Response $response, string $context): Response
    {
        if ($response->successful()) {
            return $response;
        }

        $message = sprintf('%s: %s failed with HTTP %d', $this->platform()->label(), $context, $response->status());
        $detail = $response->json('error.message') ?? $response->json('error_description') ?? $response->json('detail') ?? $response->json('title');

        if (is_string($detail) && $detail !== '') {
            $message .= " ({$detail})";
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new ReconnectionRequiredException($message);
        }

        throw new ConnectorException($message);
    }

    protected function int(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function float(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
