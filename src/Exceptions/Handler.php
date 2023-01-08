<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as IlluminateHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends IlluminateHandler
{
    /**
     * HTTP exception message.
     */
    final public const ERROR_MESSAGE_UNEXPECTED_ERROR = 'Unexpected Error';

    /**
     * Status messages map.
     */
    public const STATUS_MESSAGES = [
        419 => 'Csrf Token Mismatch',
        MustBeGuestHttpException::ERROR_STATUS => MustBeGuestHttpException::ERROR_MESSAGE,
    ];

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
     *
     * @param array<mixed> $headers
     */
    public function httpException(int $status, string $message, Throwable $exception, array $headers = []): HttpExceptionInterface
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception;
        }

        $code = 0;
        $previousCode = $exception->getCode();

        if (\is_int($previousCode)) {
            $code = $previousCode;
        }

        return match (true) {
            $exception instanceof BackedEnumCaseNotFoundException => new HttpException(404, SymfonyResponse::$statusTexts[404], $exception, $headers, $code),
            $exception instanceof ModelNotFoundException => new HttpException(404, SymfonyResponse::$statusTexts[404], $exception, $headers, $code),
            $exception instanceof AuthorizationException => new HttpException($exception->status() ?? 403, $exception->getMessage() !== '' && $exception->getMessage() !== 'This action is unauthorized.' ? $exception->getMessage() : '', $exception, $headers, $code),
            $exception instanceof TokenMismatchException => new HttpException(419, 'Csrf Token Mismatch', $exception, $headers, $code),
            $exception instanceof SuspiciousOperationException => new HttpException(404, 'Bad Hostname Provided', $exception, $headers, $code),
            $exception instanceof RecordsNotFoundException => new HttpException(404, SymfonyResponse::$statusTexts[404], $exception, $headers, $code),
            default => new HttpException($status, $message, $exception, $headers, $code),
        };
    }

    /**
     * Get HTTP exception message.
     */
    public function httpExceptionMessage(HttpExceptionInterface $httpException): string
    {
        return $httpException->getMessage() !== '' ? $httpException->getMessage() : static::STATUS_MESSAGES[$httpException->getStatusCode()] ?? SymfonyResponse::$statusTexts[$httpException->getStatusCode()] ?? static::ERROR_MESSAGE_UNEXPECTED_ERROR;
    }

    /**
     * @inheritDoc
     */
    protected function prepareException(Throwable $e): Throwable
    {
        return $e;
    }

    /**
     * @inheritDoc
     */
    protected function unauthenticated(mixed $request, AuthenticationException $exception): SymfonyResponse
    {
        $httpException = $this->httpException(401, $exception->getMessage() !== '' && $exception->getMessage() !== 'Unauthenticated.' ? $exception->getMessage() : '', $exception);

        if ($this->shouldReturnJson($request, $exception)) {
            return $this->jsonResponse($request, $exception, $httpException);
        }

        if (resolveApp()->hasDebugModeEnabled()) {
            return $this->debugResponse($request, $exception, $httpException);
        }

        $to = $exception->redirectTo() ?? $this->loginUrl($exception);

        if ($to !== null && resolveUrlFactory()->current() !== $to) {
            return resolveRedirector()->guest($to);
        }

        return $this->viewResponse($request, $exception, $httpException);
    }

    /**
     * @inheritDoc
     */
    protected function invalidJson(mixed $request, ValidationException $exception): JsonResponse
    {
        $httpException = $this->httpException($exception->status, 'The Given Data Was Invalid', $exception);

        return $this->jsonResponse($request, $exception, $httpException, [
            'errors' => $exception->errors(),
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function invalid(mixed $request, ValidationException $exception): Response
    {
        if (resolveApp()->hasDebugModeEnabled()) {
            $httpException = $this->httpException($exception->status, 'The Given Data Was Invalid', $exception);

            return $this->debugResponse($request, $exception, $httpException);
        }

        return parent::invalid($request, $exception);
    }

    /**
     * @inheritDoc
     */
    protected function prepareResponse(mixed $request, Throwable $e): SymfonyResponse
    {
        $httpException = $this->httpException(500, SymfonyResponse::$statusTexts[500], $e);

        if (resolveApp()->hasDebugModeEnabled()) {
            return $this->debugResponse($request, $e, $httpException);
        }

        return $this->viewResponse($request, $e, $httpException);
    }

    /**
     * @inheritDoc
     */
    protected function prepareJsonResponse(mixed $request, Throwable $e): JsonResponse
    {
        $httpException = $this->httpException(500, SymfonyResponse::$statusTexts[500], $e);

        return $this->jsonResponse($request, $e, $httpException);
    }

    /**
     * Encode json response.
     *
     * @param array<mixed> $data
     */
    protected function jsonResponse(Request $request, Throwable $exception, HttpExceptionInterface $httpException, array $data = []): JsonResponse
    {
        $json = $this->convertExceptionToArray($exception);

        unset($json['message']);

        if (resolveApp()->hasDebugModeEnabled()) {
            if ($exception->getMessage() === $httpException->getMessage()) {
                $internal = \trim($exception->getMessage());
            } else {
                $internal = \trim("{$exception->getMessage()} {$httpException->getMessage()}");
            }

            if (blank($internal)) {
                $internal = $this->httpExceptionMessage($httpException);
            }

            $json['internal'] = $internal;
        }

        $json['status'] = $httpException->getStatusCode();
        $json['code'] = $httpException->getCode();

        return new JsonResponse(
            \array_replace($json, $data),
            $httpException->getStatusCode(),
            $httpException->getHeaders(),
        );
    }

    /**
     * Where to redirect when unauthenticated.
     */
    protected function loginUrl(AuthenticationException $exception): ?string
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
    protected function shouldReturnJson(mixed $request, Throwable $e): bool
    {
        return parent::shouldReturnJson($request, $e) || $request->getRequestFormat() === 'json';
    }

    /**
     * Send debug response.
     */
    protected function debugResponse(Request $request, Throwable $exception, HttpExceptionInterface $httpException): Response
    {
        $response = new SymfonyResponse(
            $this->renderExceptionContent($exception),
            $httpException->getStatusCode(),
            $httpException->getHeaders(),
        );

        return $this->toIlluminateResponse($response, $exception)->prepare($request);
    }

    /**
     * Send view response.
     */
    protected function viewResponse(Request $request, Throwable $exception, HttpExceptionInterface $httpException): SymfonyResponse
    {
        $response = resolveResponseFactory()->view('exceptions::xxx', $this->viewData($request, $exception, $httpException), $httpException->getStatusCode(), $httpException->getHeaders());

        return $this->toIlluminateResponse($response, $exception)->prepare($request);
    }

    /**
     * Get data passed to HTTP exception view.
     *
     * @return array<mixed>
     */
    protected function viewData(Request $request, Throwable $exception, HttpExceptionInterface $httpException): array
    {
        $message = $this->httpExceptionMessage($httpException);
        $title = $this->httpExceptionTitle($httpException);

        return [
            'errors' => new ViewErrorBag(),
            'exception' => $exception,
            'httpException' => $httpException,
            'message' => $message,
            'title' => $title,
        ];
    }

    /**
     * Get HTTP exception title.
     */
    protected function httpExceptionTitle(HttpExceptionInterface $httpException): string
    {
        $message = $this->httpExceptionMessage($httpException);

        $translator = resolveTranslator();

        $messageToTry = "exceptions::titles.{$message}";

        if ($translator->has($messageToTry)) {
            return mustTransString($messageToTry);
        }

        $normalizedMessage = $this->normalizeMessage($message);
        $messageToTry = "exceptions::titles.{$normalizedMessage}";

        if ($translator->has($messageToTry)) {
            return mustTransString($messageToTry);
        }

        if ($translator->has($message)) {
            return mustTransString($message);
        }

        if ($translator->has($normalizedMessage)) {
            return mustTransString($normalizedMessage);
        }

        $message = static::STATUS_MESSAGES[$httpException->getStatusCode()] ?? SymfonyResponse::$statusTexts[$httpException->getStatusCode()] ?? static::ERROR_MESSAGE_UNEXPECTED_ERROR;

        return mustTransString("exceptions::titles.{$message}");
    }

    /**
     * Normalize message.
     */
    protected function normalizeMessage(string $message): string
    {
        return Str::title(extendedTrim($message, '.'));
    }
}
