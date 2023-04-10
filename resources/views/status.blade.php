@php
  $locale = \str_replace('_', '-', resolveApp()->getLocale());

  $viewService = Tomchochola\Laratchi\View\Services\ViewService::inject();

  $color = $viewService->color();

  $illustration = $viewService->illustration($status);

  $background = $viewService->background();
  $nightBackground = $viewService->nightBackground();
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8" />

  <meta name="viewport" content="width=device-width,initial-scale=1" />

  <meta name="robots" content="noindex, nofollow" />

  <meta name="author" content="Tomáš Chochola <chocholatom1997@gmail.com>" />

  <title>{{ $title }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;900&display=swap" rel="stylesheet" />

  <style>
    @include('laratchi::css')

    body {
      word-break: break-word;
    }
  </style>
</head>
<body class="bg-white dark:bg-[#49454F] text-gray-900 antialiased">
  <div class="px-4 py-8 min-h-screen relative flex overflow-hidden">
    <div class="flex justify-center items-center lg:w-1/2 flex-grow">
      <div class="z-30">
        <div class="text-9xl md:text-[10rem] font-black mb-2">{{ $status }}</div>
        <div class="w-1/2 border-b-8 rounded-lg mb-8" style="border-color: {{ $color }}" role="presentation"></div>
        <div class="text-3xl max-w-sm mb-8">{{ $title }}</div>
      </div>
    </div>

    <div class="justify-center items-center hidden lg:flex w-1/2" role="presentation">
      <div class="w-4/6 relative z-30 flex justify-center items-center">
        <img alt="{{ $title }}" src="{{ $illustration }}" style="width: 100%;" />
      </div>
    </div>

    <div class="absolute inset-0 lg:w-1/2 z-20 bg-white opacity-50" role="presentation"></div>

    <div class="dark:hidden absolute inset-0 lg:w-1/2 bg-cover bg-center bg-no-repeat z-10" style="background-image: url({{ $background }})" role="presentation"></div>
    <div class="hidden dark:block absolute inset-0 lg:w-1/2 bg-cover bg-center bg-no-repeat z-10" style="background-image: url({{ $nightBackground }})" role="presentation"></div>
  </div>
</body>
</html>
