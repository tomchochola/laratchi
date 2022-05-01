@php
  \assert(isset($exception) && $exception instanceof Symfony\Component\HttpKernel\Exception\HttpExceptionInterface);

  $locale = \str_replace('_', '-', resolveApp()->getLocale());

  $statusCode = (string) $exception->getStatusCode();

  $color = Tomchochola\Laratchi\Exceptions\Handler::color($exception);

  $title = Tomchochola\Laratchi\Exceptions\Handler::title($exception);

  $phase = Tomchochola\Laratchi\Exceptions\Handler::sunPhase($exception);

  $favicon = Tomchochola\Laratchi\Exceptions\Handler::faviconPath($exception);

  $homePath = Tomchochola\Laratchi\Exceptions\Handler::homePath($exception);

  $goHome = Tomchochola\Laratchi\Exceptions\Handler::goHome($exception);

  $viewFactory = resolveViewFactory();

  $illustration = 'data:image/svg+xml;utf8,'.\rawurlencode($viewFactory->first(["exceptions::illustrations.{$statusCode}", 'exceptions::illustrations.'.\substr($statusCode, 0, -2).'xx', 'exceptions::illustrations.5xx'], ['color' => $color])->render());

  $background = 'data:image/svg+xml;utf8,'.\rawurlencode($viewFactory->make('exceptions::phases.day')->render());
  $nightBackground = 'data:image/svg+xml;utf8,'.\rawurlencode($viewFactory->make('exceptions::phases.night')->render());
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">
  <head>
    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="robots" content="noindex, nofollow" />

    <title>{{ $title }}</title>

    <link rel="icon" href="{{ $favicon }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;900&display=swap" rel="stylesheet" />

    <style>
      .button:hover,
      .button:focus {
        border-color: {{ $color }};
        background-color: {{ $color }};
      }

      @include('exceptions::css')
    </style>
  </head>
  <body class="bg-white dark:bg-[#49454F] text-gray-900 antialiased">
    <div class="px-4 py-8 min-h-screen relative flex overflow-hidden">
      <div class="flex justify-center items-center lg:w-1/2 flex-grow">
        <div class="z-30">
          <div class="text-9xl md:text-[10rem] font-black mb-2">{{ $statusCode }}</div>
          <div class="w-1/2 border-b-8 rounded-lg mb-8" style="border-color: {{ $color }}" role="presentation"></div>
          <div class="text-3xl max-w-sm mb-8">{{ $title }}</div>
          @if($homePath !== '' && resolveUrlFactory()->current() !== $homePath)
            <a
              href="{{ $homePath }}"
              class="button inline-block animate-bounce transition px-4 py-2 border border-gray-900 focus:outline-none focus:text-white rounded-lg hover:text-white text-2xl"
            >
              {{ $goHome }}
            </a>
          @endif
        </div>
      </div>

      <div class="justify-center items-center hidden lg:flex w-1/2" role="presentation">
        <div class="w-4/6 relative z-30 flex justify-center items-center">
          <img alt="{{ $title }}" src="{{ $illustration }}" />
        </div>
      </div>

      <div class="absolute inset-0 lg:w-1/2 z-20 bg-white opacity-50" role="presentation"></div>

      <div class="dark:hidden absolute inset-0 lg:w-1/2 bg-cover bg-center bg-no-repeat z-10" style="background-image: url({{ $background }})" role="presentation"></div>
      <div class="hidden dark:block absolute inset-0 lg:w-1/2 bg-cover bg-center bg-no-repeat z-10" style="background-image: url({{ $nightBackground }})" role="presentation"></div>
    </div>
  </body>
</html>
