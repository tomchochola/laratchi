@php
  \assert(isset($httpException) && $httpException instanceof Symfony\Component\HttpKernel\Exception\HttpExceptionInterface);

  $status = $status ?? $httpException->getStatusCode();
  $title = $title ?? $httpException->getMessage();

  if (! isset($color)) {
    $color = inject(Tomchochola\Laratchi\View\Services\ViewService::class)->color();
  }

  if (! isset($illustration)) {
    $illustration = 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->first(["exceptions::illustrations.{$status}", 'exceptions::illustrations.'.\substr($status, 0, -2).'xx', 'exceptions::illustrations.5xx'], ['color' => $color])->render());
  }
@endphp

@include('laratchi::status', [
  'locale' => $locale ?? null,
  'color' => $color,
  'favicon' => $favicon ?? null,
  'illustration' => $illustration,
  'background' => $background ?? null,
  'nightBackground' => $nightBackground ?? null,
  'title' => $title,
  'status' => $status,
  'buttonUrl' => $buttonUrl ?? null,
  'buttonLabel' => $buttonLabel ?? null,
  'noindex' => true,
])
