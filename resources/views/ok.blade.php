@include('laratchi::status', [
  'locale' => $locale ?? null,
  'color' => $color ?? null,
  'favicon' => $favicon ?? null,
  'illustration' => $illustration ?? null,
  'background' => $background ?? null,
  'nightBackground' => $nightBackground ?? null,
  'title' => $title ?? null,
  'status' => $status ?? 'OK',
  'buttonUrl' => $buttonUrl ?? null,
  'buttonLabel' => $buttonLabel ?? null,
  'noindex' => $noindex ?? null,
])
