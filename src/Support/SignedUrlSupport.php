<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

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
            $parameters['expires'] = \resolveDate()
                ->now()
                ->addMinutes($expires)
                ->getTimestamp();
        }

        \ksort($parameters);

        return \resolveUrlFactory()->action(
            $action,
            \array_replace($parameters, [
                'signature' => \hash_hmac('sha256', \resolveUrlFactory()->action($action, $parameters), \mustConfigString('app.key')),
            ]),
        );
    }
}
