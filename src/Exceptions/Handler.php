<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as IlluminateHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends IlluminateHandler
{
    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE_UNEXPECTED_ERROR = 'Unexpected Error';

    /**
     * @inheritDoc
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'new_password',
        'new_password_confirmation',
    ];

    /**
     * Convert throwable to HTTP exception.
     */
    public static function httpException(int $status, Throwable $previous, string $message): HttpExceptionInterface
    {
        if ($previous instanceof HttpExceptionInterface) {
            return $previous;
        }

        $code = 0;

        if (\is_int($previous->getCode())) {
            $code = $previous->getCode();
        }

        return new HttpException($status, $message, $previous, [], $code);
    }

    /**
     * Get favicon public path.
     */
    public static function faviconPath(HttpExceptionInterface $exception): string
    {
        return resolveUrlFactory()->asset('favicon.ico');
    }

    /**
     * Get error message.
     */
    public static function message(HttpExceptionInterface $e): string
    {
        return $e->getMessage() !== '' ? $e->getMessage() : SymfonyResponse::$statusTexts[$e->getStatusCode()] ?? static::ERROR_MESSAGE_UNEXPECTED_ERROR;
    }

    /**
     * Get error title.
     */
    public static function title(HttpExceptionInterface $e): string
    {
        $message = static::message($e);

        $normalizedMessage = static::normalizeMessage($message);

        if (resolveTranslator()->has("exceptions::titles.{$normalizedMessage}")) {
            return mustTransString("exceptions::titles.{$normalizedMessage}");
        }

        return $message;
    }

    /**
     * Home path for current exception.
     */
    public static function homePath(HttpExceptionInterface $exception): string
    {
        $homeRoute = 'home';

        $router = resolveRouter();
        $urlFactory = resolveUrlFactory();

        if ($router->has($homeRoute)) {
            return $urlFactory->route($homeRoute);
        }

        return $urlFactory->to('/');
    }

    /**
     * Go home button label.
     */
    public static function goHome(HttpExceptionInterface $exception): string
    {
        return mustTransJsonString('Go Home');
    }

    /**
     * Get color used in svgs.
     */
    public static function color(HttpExceptionInterface $exception): string
    {
        return '#6c63ff';
    }

    /**
     * Get actual sun phase.
     *
     * @return 'day'|'sunrise'|'sunset'|'night'
     */
    public static function sunPhase(HttpExceptionInterface $exception): string
    {
        $timestamp = Carbon::now()->getTimestamp();

        [$lat, $lng] = static::sunLatLng($exception);

        $sun = \date_sun_info($timestamp, $lat, $lng);

        $sunrise = (int) $sun['sunrise'];
        $sunset = (int) $sun['sunset'];
        $mid = (int) $sun['transit'];

        $riseDuration = ($mid - $sunrise) / 2;

        if ($timestamp >= $sunrise && $timestamp <= $sunrise + $riseDuration) {
            return 'sunrise';
        }

        if ($timestamp >= $sunrise && $timestamp <= $sunset - $riseDuration) {
            return 'day';
        }

        if ($timestamp > $sunset || $timestamp < $sunrise) {
            return 'night';
        }

        return 'sunset';
    }

    /**
     * Normalize message.
     */
    protected static function normalizeMessage(string $message): string
    {
        return Str::title(\trim($message, " \t\n\r\0\x0B."));
    }

    /**
     * Get lat, lng for sun phase.
     *
     * @return array{float, float}
     */
    protected static function sunLatLng(HttpExceptionInterface $exception): array
    {
        return [
            50.075538,
            14.437801,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareException(Throwable $e): Throwable
    {
        return match (true) {
            $e instanceof BackedEnumCaseNotFoundException => static::httpException(404, $e, SymfonyResponse::$statusTexts[404]),
            $e instanceof ModelNotFoundException => static::httpException(404, $e, SymfonyResponse::$statusTexts[404]),
            $e instanceof AuthorizationException => static::httpException(403, $e, $e->getMessage() !== '' && $e->getMessage() !== 'This action is unauthorized.' ? $e->getMessage() : SymfonyResponse::$statusTexts[403]),
            $e instanceof TokenMismatchException => static::httpException(419, $e, 'Csrf Token Mismatch'),
            $e instanceof SuspiciousOperationException => static::httpException(404, $e, 'Bad Hostname Provided'),
            $e instanceof RecordsNotFoundException => static::httpException(404, $e, SymfonyResponse::$statusTexts[404]),
            default => parent::prepareException($e),
        };
    }

    /**
     * @inheritDoc
     */
    protected function unauthenticated(mixed $request, AuthenticationException $exception): SymfonyResponse
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return $this->jsonResponse(static::httpException(401, $exception, SymfonyResponse::$statusTexts[401]));
        }

        if ($this->debug()) {
            return $this->response(static::httpException(401, $exception, SymfonyResponse::$statusTexts[401]));
        }

        $to = $exception->redirectTo() ?? $this->loginPath($exception);

        if (resolveUrlFactory()->current() !== $to) {
            return resolveRedirector()->guest($to);
        }

        return $this->response(static::httpException(401, $exception, SymfonyResponse::$statusTexts[401]));
    }

    /**
     * Where to redirect when unauthenticated.
     */
    protected function loginPath(AuthenticationException $exception): string
    {
        $route = 'login';

        $router = resolveRouter();
        $urlFactory = resolveUrlFactory();

        if ($router->has($route)) {
            return $urlFactory->route($route);
        }

        return $urlFactory->to('/');
    }

    /**
     * @inheritDoc
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, mixed $request): SymfonyResponse
    {
        if ($e->response !== null) {
            return $e->response;
        }

        if ($this->shouldReturnJson($request, $e)) {
            return $this->jsonResponse(static::httpException($e->status, $e, 'The Given Data Was Invalid'), [
                'errors' => $e->errors(),
            ]);
        }

        if ($this->debug()) {
            return $this->response(static::httpException($e->status, $e, 'The Given Data Was Invalid'));
        }

        return $this->invalid($request, $e);
    }

    /**
     * @inheritDoc
     */
    protected function renderExceptionResponse(mixed $request, Throwable $e): SymfonyResponse
    {
        if (! $e instanceof HttpExceptionInterface) {
            $e = static::httpException(500, $e, SymfonyResponse::$statusTexts[500]);
        }

        if ($this->shouldReturnJson($request, $e)) {
            return $this->jsonResponse($e);
        }

        return $this->response($e);
    }

    /**
     * Prepare a response for the given exception.
     */
    protected function response(HttpExceptionInterface $e): SymfonyResponse
    {
        if ($this->debug()) {
            $response = new SymfonyResponse(
                $this->renderExceptionContent($e->getPrevious() ?? $e),
                $e->getStatusCode(),
                $e->getHeaders(),
            );

            return $this->toIlluminateResponse($response, $e);
        }

        return $this->toIlluminateResponse(
            $this->renderHttpException($e),
            $e,
        );
    }

    /**
     * Prepare a JSON response for the given HTTP exception.
     *
     * @param array<string, mixed> $data
     */
    protected function jsonResponse(HttpExceptionInterface $e, array $data = []): JsonResponse
    {
        return new JsonResponse(
            \array_replace($this->convertHttpExceptionToArray($e), $data),
            $e->getStatusCode(),
            $e->getHeaders(),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES,
        );
    }

    /**
     * Convert the given HTTP exception to an array.
     *
     * @return array<string, mixed>
     */
    protected function convertHttpExceptionToArray(HttpExceptionInterface $e): array
    {
        $debug = $this->debug();
        $previous = $e->getPrevious() ?? $e;

        $data = parent::convertExceptionToArray($previous);

        $message = static::message($e);

        if ($debug) {
            $data['internal'] = $previous->getMessage() !== '' ? $previous->getMessage() : $message;
        }

        $normalizedMessage = static::normalizeMessage($message);

        $title = static::title($e);

        $data['status'] = $e->getStatusCode();
        $data['code'] = $e->getCode();
        $data['title'] = $title;
        $data['message'] = $normalizedMessage;

        return $data;
    }

    /**
     * Debug mode enabled.
     */
    protected function debug(): bool
    {
        return resolveApp()->hasDebugModeEnabled();
    }

    /**
     * @inheritDoc
     */
    protected function getHttpExceptionView(HttpExceptionInterface $e): ?string
    {
        return 'exceptions::xxx';
    }

    /**
     * @inheritDoc
     */
    protected function shouldReturnJson(mixed $request, Throwable $e): bool
    {
        return parent::shouldReturnJson($request, $e) || $request->getRequestFormat() === 'json';
    }
}
