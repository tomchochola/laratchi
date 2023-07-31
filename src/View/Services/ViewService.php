<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\View\Services;

class ViewService
{
    /**
     * Templates.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Inject.
     */
    public static function inject(): self
    {
        return new static::$template();
    }

    /**
     * Get color theme.
     */
    public function color(): string
    {
        return '#6c63ff';
    }

    /**
     * Get background.
     */
    public function background(): string
    {
        $phases = ['day', 'sunrise', 'sunset', 'night'];

        $phase = $phases[\array_rand($phases)];

        return 'data:image/svg+xml;utf8,'.
            \rawurlencode(
                resolveViewFactory()
                    ->make("laratchi::phases.{$phase}", ['color' => $this->color()])
                    ->render(),
            );
    }

    /**
     * Get night background.
     */
    public function nightBackground(): string
    {
        return 'data:image/svg+xml;utf8,'.
            \rawurlencode(
                resolveViewFactory()
                    ->make('laratchi::phases.night', ['color' => $this->color()])
                    ->render(),
            );
    }

    /**
     * Get illustration.
     */
    public function illustration(int $status): string
    {
        return 'data:image/svg+xml;utf8,'.
            \rawurlencode(
                resolveViewFactory()
                    ->first(
                        ["laratchi::illustrations.{$status}", 'laratchi::illustrations.'.\mb_substr((string) $status, 0, -2).'xx', 'laratchi::illustrations.1xx'],
                        ['color' => $this->color()],
                    )
                    ->render(),
            );
    }
}
