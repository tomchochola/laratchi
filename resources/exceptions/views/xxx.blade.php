@php
  $status = $status ?? $httpException->getStatusCode();
  $title = $title ?? $httpException->getMessage();
  $code = $code ?? $httpException->getCode();
@endphp

@include('laratchi::status', [
  'locale' => $locale ?? null,
  'color' => $color ?? null,
  'favicon' => $favicon ?? null,
  'illustration' => $illustration ?? null,
  'background' => $background ?? null,
  'nightBackground' => $nightBackground ?? null,
  'title' => $title,
  'status' => $status,
  'code' => $code,
  'buttonUrl' => $buttonUrl ?? null,
  'buttonLabel' => $buttonLabel ?? null,
  'noindex' => true,
])
