<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Support\Carbon;

class SignedUrlSupport
{
    /**
     * Make signed or temporary signed action url.
     *
     * @param array<mixed> $parameters
     */
    public static function make(string $action, array $parameters, int $expires): string
    {
        if ($expires > 0) {
            $parameters['expires'] = Carbon::now()->addMinutes($expires)->getTimestamp();
        }

        \ksort($parameters);

        return resolveUrlFactory()->action($action, \array_merge($parameters, [
            'signature' => \hash_hmac('sha256', resolveUrlFactory()->action($action, $parameters), mustConfigString('app.key')),
        ]));
    }
}
