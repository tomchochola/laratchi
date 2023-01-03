<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Exceptions\Handler;

return [
    // 100
    SymfonyResponse::$statusTexts[100] => 'Continue',

    // 101
    SymfonyResponse::$statusTexts[101] => 'Switching protocols',

    // 102
    SymfonyResponse::$statusTexts[102] => 'Processing',

    // 103
    SymfonyResponse::$statusTexts[103] => 'Early hints',

    // 200
    SymfonyResponse::$statusTexts[200] => 'OK',

    // 201
    SymfonyResponse::$statusTexts[201] => 'Created',

    // 202
    SymfonyResponse::$statusTexts[202] => 'Accepted',

    // 203
    SymfonyResponse::$statusTexts[203] => 'Non-authoritative information',

    // 204
    SymfonyResponse::$statusTexts[204] => 'No content',

    // 205
    SymfonyResponse::$statusTexts[205] => 'Reset content',

    // 206
    SymfonyResponse::$statusTexts[206] => 'Partial content',

    // 207
    SymfonyResponse::$statusTexts[207] => 'Multi-status',

    // 208
    SymfonyResponse::$statusTexts[208] => 'Already reported',

    // 226
    SymfonyResponse::$statusTexts[226] => 'IM used',

    // 300
    SymfonyResponse::$statusTexts[300] => 'Multiple choices',

    // 301
    SymfonyResponse::$statusTexts[301] => 'Moved permanently',

    // 302
    SymfonyResponse::$statusTexts[302] => 'Found',

    // 303
    SymfonyResponse::$statusTexts[303] => 'See other',

    // 304
    SymfonyResponse::$statusTexts[304] => 'Not modified',

    // 305
    SymfonyResponse::$statusTexts[305] => 'Use proxy',

    // 307
    SymfonyResponse::$statusTexts[307] => 'Temporary redirect',

    // 308
    SymfonyResponse::$statusTexts[308] => 'Permanent redirect',

    // 400
    SymfonyResponse::$statusTexts[400] => 'Bad request',

    // 401
    SymfonyResponse::$statusTexts[401] => 'Sorry, you are not authorized to access this page',

    // 402
    SymfonyResponse::$statusTexts[402] => 'Payment required',

    // 403
    SymfonyResponse::$statusTexts[403] => 'Sorry, you are forbidden from accessing this page',

    // 403
    'Invalid Credentials' => 'Invalid credentials',

    // 403
    'Invalid Signature' => 'Invalid signature',

    // 404
    SymfonyResponse::$statusTexts[404] => 'Sorry, the page you are looking for could not be found',

    // 404
    'Bad Hostname Provided' => 'Bad hostname provided',

    // 405
    SymfonyResponse::$statusTexts[405] => 'Method not allowed',

    // 406
    SymfonyResponse::$statusTexts[406] => 'Not acceptable',

    // 407
    SymfonyResponse::$statusTexts[407] => 'Proxy authentication required',

    // 408
    SymfonyResponse::$statusTexts[408] => 'Request timeout',

    // 409
    SymfonyResponse::$statusTexts[409] => 'Conflict',

    // 410
    SymfonyResponse::$statusTexts[410] => 'Gone',

    // 411
    SymfonyResponse::$statusTexts[411] => 'Length required',

    // 412
    SymfonyResponse::$statusTexts[412] => 'Precondition failed',

    // 413
    SymfonyResponse::$statusTexts[413] => 'Payload too large',

    // 414
    SymfonyResponse::$statusTexts[414] => 'URI too long',

    // 415
    SymfonyResponse::$statusTexts[415] => 'Unsupported media type',

    // 416
    SymfonyResponse::$statusTexts[416] => 'Range not satisfiable',

    // 417
    SymfonyResponse::$statusTexts[417] => 'Expectation failed',

    // 418
    SymfonyResponse::$statusTexts[418] => 'I\'m a teapot',

    // 419
    'Csrf Token Mismatch' => 'Sorry, your session has expired, please refresh and try again',

    // 421
    SymfonyResponse::$statusTexts[421] => 'Misdirected request',

    // 422
    SymfonyResponse::$statusTexts[422] => 'Unprocessable entity',

    // 422
    'The Given Data Was Invalid' => 'The given data was invalid',

    // 423
    SymfonyResponse::$statusTexts[423] => 'Locked',

    // 424
    SymfonyResponse::$statusTexts[424] => 'Failed dependency',

    // 425
    SymfonyResponse::$statusTexts[425] => 'Too early',

    // 426
    SymfonyResponse::$statusTexts[426] => 'Upgrade required',

    // 428
    SymfonyResponse::$statusTexts[428] => 'Precondition required',

    // 429
    SymfonyResponse::$statusTexts[429] => 'Sorry, you are making too many requests to our servers',

    // 431
    SymfonyResponse::$statusTexts[431] => 'Request header fields too large',

    // 451
    SymfonyResponse::$statusTexts[451] => 'Unavailable for legal reasons',

    // 500
    SymfonyResponse::$statusTexts[500] => 'Whoops, something went wrong on our servers',

    // 501
    SymfonyResponse::$statusTexts[501] => 'Not implemented',

    // 502
    SymfonyResponse::$statusTexts[502] => 'Bad gateway',

    // 503
    SymfonyResponse::$statusTexts[503] => 'Sorry, we are doing some maintenance, please check back soon',

    // 504
    SymfonyResponse::$statusTexts[504] => 'Gateway timeout',

    // 505
    SymfonyResponse::$statusTexts[505] => 'HTTP version not supported',

    // 506
    SymfonyResponse::$statusTexts[506] => 'Variant aso negotiate',

    // 507
    SymfonyResponse::$statusTexts[507] => 'Insufficient storage',

    // 508
    SymfonyResponse::$statusTexts[508] => 'Loop detected',

    // 510
    SymfonyResponse::$statusTexts[510] => 'Not extended',

    // 511
    SymfonyResponse::$statusTexts[511] => 'Network authentication required',

    // Fallback
    Handler::ERROR_MESSAGE_UNEXPECTED_ERROR => 'Whoops, something went wrong',

    // 400
    Tomchochola\Laratchi\Http\Middleware\MustBeStatefulMiddleware::ERROR_MESSAGE => 'Request must be stateful',

    // 400
    Tomchochola\Laratchi\Http\Middleware\MustBeStatelessMiddleware::ERROR_MESSAGE => 'Request must be stateless',

    // 427
    Tomchochola\Laratchi\Exceptions\MustBeGuestHttpException::ERROR_MESSAGE => 'Log out first',
];
