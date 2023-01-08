@php
  $locale = \str_replace('_', '-', $locale ?? resolveApp()->getLocale());

  $viewService = inject(Tomchochola\Laratchi\View\Services\ViewService::class);

  $color = $color ?? $viewService->color();

  $favicon = $favicon ?? $viewService->faviconUrl();

  $illustration = $illustration ?? $viewService->illustration($color);

  $background = $background ?? $viewService->background($color);
  $nightBackground = $nightBackground ?? $viewService->nightBackground($color);

  $noindex = $noindex ?? false;
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
  <meta charset="UTF-8"/>

  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  @if($noindex === true)
  <meta name="robots" content="noindex, nofollow"/>
  @endif

  <title>{{ $title ?? $status }}</title>

  @isset($favicon)
  <link rel="icon" href="{{ $favicon }}"/>
  @endisset

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;900&display=swap" rel="stylesheet"/>

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
        @isset($status)
        <div class="text-9xl md:text-[10rem] font-black mb-2">{{ $status }}</div>
          <div class="w-1/2 border-b-8 rounded-lg mb-8" style="border-color: {{ $color }}" role="presentation"></div>
        @endisset
        @isset($title)
        <div class="text-3xl max-w-sm mb-8">{{ $title }}</div>
        @endisset
        @if(isset($buttonUrl) && resolveUrlFactory()->current() !== $buttonUrl)
          <a
            href="{{ $buttonUrl }}"
            class="button inline-block animate-bounce transition px-4 py-2 border border-gray-900 focus:outline-none focus:text-white rounded-lg hover:text-white text-2xl"
          >
            {{ $buttonLabel ?? $buttonUrl }}
          </a>
        @endif
      </div>
    </div>

    <div class="justify-center items-center hidden lg:flex w-1/2" role="presentation">
      <div class="w-4/6 relative z-30 flex justify-center items-center">
        <img alt="{{ $title ?? $status }}" src="{{ $illustration }}" style="width: 100%;"/>
      </div>
    </div>

    <div class="absolute inset-0 lg:w-1/2 z-20 bg-white opacity-50" role="presentation"></div>

    <div class="dark:hidden absolute inset-0 lg:w-1/2 bg-cover bg-center bg-no-repeat z-10" style="background-image: url({{ $background }})" role="presentation"></div>
    <div class="hidden dark:block absolute inset-0 lg:w-1/2 bg-cover bg-center bg-no-repeat z-10" style="background-image: url({{ $nightBackground }})" role="presentation"></div>
  </div>
</body>
</html>
