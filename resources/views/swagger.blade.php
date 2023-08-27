@php
  $config = Tomchochola\Laratchi\Config\Config::inject();

  $appName = $config->appName();
@endphp

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <meta name="robots" content="noindex, nofollow" />

    <meta name="author" content="Tomáš Chochola <chocholatom1997@gmail.com>" />

    <title>{{ $appName }}</title>

    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@4/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@4/favicon-16x16.png" sizes="16x16" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@4/index.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@4/swagger-ui.min.css" />
  </head>
  <body>
    <div id="swagger"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@4/swagger-ui-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@4/swagger-ui-standalone-preset.min.js"></script>
    <script>
      window.onload = function () {
        window.ui = SwaggerUIBundle({
          url: {!! "'{$url}'" !!},
          spec: undefined,
          dom_id: '#swagger',
          deepLinking: true,
          presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
          plugins: [SwaggerUIBundle.plugins.DownloadUrl],
          layout: 'StandaloneLayout',
        });
      };
    </script>
  </body>
</html>
