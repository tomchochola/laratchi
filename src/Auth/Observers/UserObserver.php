<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Observers;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Auth\Actions\LogoutOtherDevicesAction;
use Tomchochola\Laratchi\Auth\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if ($user->getAuthPassword() === '') {
            $this->sendPasswordInit($user);
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        if ($user->isDirty('email') && $user instanceof MustVerifyEmailContract && $user->hasVerifiedEmail()) {
            $this->clearEmailVerifiedAt($user);
        }

        if ($user->isDirty('password')) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('email') && $user instanceof MustVerifyEmailContract && ! $user->hasVerifiedEmail()) {
            $this->sendEmailVerificationNotification($user);
        }

        if ($user->wasChanged('email') && $user->getAuthPassword() === '') {
            $this->sendPasswordInit($user);
        }

        if ($user->wasChanged('password')) {
            $this->logoutOtherDevices($user);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->clearNotifications($user);
        $this->logoutOtherDevices($user);
    }

    /**
     * Send password init.
     */
    protected function sendPasswordInit(User $user): void
    {
        $user->sendPasswordResetNotification(resolvePasswordBroker($user->getPasswordBrokerName())->createToken($user));
    }

    /**
     * Clear email verified at field.
     */
    protected function clearEmailVerifiedAt(User&MustVerifyEmailContract $user): void
    {
        $user->setAttribute('email_verified_at', null);
    }

    /**
     * Cycle remember token.
     */
    protected function cycleRememberToken(User $user): void
    {
        inject(CycleRememberTokenAction::class)->handle($user);
    }

    /**
     * Send email verification notification.
     */
    protected function sendEmailVerificationNotification(User&MustVerifyEmailContract $user): void
    {
        $user->sendEmailVerificationNotification();
    }

    /**
     * Clear notifications table.
     */
    protected function clearNotifications(User $user): void
    {
        $user->notifications()->getQuery()->delete();
    }

    /**
     * Logout other devices.
     */
    protected function logoutOtherDevices(User $user): void
    {
        inject(LogoutOtherDevicesAction::class)->handle($user);
    }
}
