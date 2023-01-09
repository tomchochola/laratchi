<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Auth\SessionGuard;

class SessionReloginAction implements ReloginActionInterface
{
    /**
     * @inheritDoc
     */
    public function handle(string $guardName): void
    {
        $guard = resolveAuthManager()->guard($guardName);
        $user = $guard->user();

        \assert($guard instanceof SessionGuard);
        \assert($user !== null);

        if (blank($user->getRememberToken())) {
            return;
        }

        $cookieName = $guard->getRecallerName();

        $cookieJar = resolveCookieJar();

        if (! resolveRequest()->hasCookie($cookieName) && ! $cookieJar->hasQueued($cookieName)) {
            return;
        }

        $id = $user->getAuthIdentifier();

        \assert(\is_scalar($id));

        $cookieJar->queue($cookieJar->forever($cookieName, $id.'|'.$user->getRememberToken().'|'.$user->getAuthPassword()));
    }
}
