<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Exceptions\Handler;

return [
    // 100
    SymfonyResponse::$statusTexts[100] => 'Pokračovat',

    // 101
    SymfonyResponse::$statusTexts[101] => 'Změna protokolů',

    // 102
    SymfonyResponse::$statusTexts[102] => 'Zpracování',

    // 103
    SymfonyResponse::$statusTexts[103] => 'Kontrolní bod',

    // 200
    SymfonyResponse::$statusTexts[200] => 'OK',

    // 201
    SymfonyResponse::$statusTexts[201] => 'Vytvořeno',

    // 202
    SymfonyResponse::$statusTexts[202] => 'Přijato',

    // 203
    SymfonyResponse::$statusTexts[203] => 'Nesměrodatná informace',

    // 204
    SymfonyResponse::$statusTexts[204] => 'Žádný obsah',

    // 205
    SymfonyResponse::$statusTexts[205] => 'Obnovit obsah',

    // 206
    SymfonyResponse::$statusTexts[206] => 'Dílčí obsah',

    // 207
    SymfonyResponse::$statusTexts[207] => 'S více stavy',

    // 208
    SymfonyResponse::$statusTexts[208] => 'Již nahlášeno',

    // 226
    SymfonyResponse::$statusTexts[226] => 'Použité IM',

    // 300
    SymfonyResponse::$statusTexts[300] => 'Dokument je dostupný na více umístěních',

    // 301
    SymfonyResponse::$statusTexts[301] => 'Trvale přesunuto',

    // 302
    SymfonyResponse::$statusTexts[302] => 'Dočasně přesunuto',

    // 303
    SymfonyResponse::$statusTexts[303] => 'Viz jiné místo',

    // 304
    SymfonyResponse::$statusTexts[304] => 'Od posledního požadavku se zdrojový dokument nezměnil',

    // 305
    SymfonyResponse::$statusTexts[305] => 'Použijte server proxy',

    // 307
    SymfonyResponse::$statusTexts[307] => 'Dočasné přesměrování',

    // 308
    SymfonyResponse::$statusTexts[308] => 'Trvalé přesměrování',

    // 400
    SymfonyResponse::$statusTexts[400] => 'Chybný požadavek',

    // 401
    SymfonyResponse::$statusTexts[401] => 'Omlouváme se, nejste oprávněni k přístupu na tuto stránku',

    // 401
    'Unauthenticated' => 'Omlouváme se, nejste oprávněni k přístupu na tuto stránku',

    // 402
    SymfonyResponse::$statusTexts[402] => 'Požadovaná platba',

    // 403
    SymfonyResponse::$statusTexts[403] => 'Omlouváme se, ale přístup na tuto stránku je zakázán',

    // 403
    'Invalid Credentials' => 'Nesprávné přístupové údaje',

    // 403
    'Invalid Signature' => 'Nesprávný podpis odkazu',

    // 403
    'This Action Is Unauthorized' => 'Omlouváme se, ale přístup na tuto stránku je zakázán',

    // 404
    SymfonyResponse::$statusTexts[404] => 'Omlouváme se, ale hledanou stránku se nepodařilo najít',

    // 404
    'Bad Hostname Provided' => 'Zadaná špatná adresa',

    // 405
    SymfonyResponse::$statusTexts[405] => 'Metoda není povolena',

    // 406
    SymfonyResponse::$statusTexts[406] => 'Nepřijatelné',

    // 407
    SymfonyResponse::$statusTexts[407] => 'Vyžaduje se ověření serverem proxy',

    // 408
    SymfonyResponse::$statusTexts[408] => 'Časový limit požadavku vypršel',

    // 409
    SymfonyResponse::$statusTexts[409] => 'Konflikt',

    // 410
    SymfonyResponse::$statusTexts[410] => 'Dokument již není dostupný',

    // 411
    SymfonyResponse::$statusTexts[411] => 'Je vyžadována délka',

    // 412
    SymfonyResponse::$statusTexts[412] => 'Předběžná podmínka se nezdařila',

    // 413
    SymfonyResponse::$statusTexts[413] => 'Požadavek je příliš rozsáhlý',

    // 414
    SymfonyResponse::$statusTexts[414] => 'Požadovaný identifikátor URI je příliš dlouhý',

    // 415
    SymfonyResponse::$statusTexts[415] => 'Nepodporovaný typ média',

    // 416
    SymfonyResponse::$statusTexts[416] => 'Požadovaný rozsah nelze uspokojit',

    // 417
    SymfonyResponse::$statusTexts[417] => 'Očekávání se nezdařilo',

    // 418
    SymfonyResponse::$statusTexts[418] => 'Jsem konvička',

    // 419
    'Csrf Token Mismatch' => 'Omlouváme se, vaše relace vypršela, obnovte ji prosím a zkuste to znovu',

    // 421
    SymfonyResponse::$statusTexts[421] => 'Nesprávně směrovaná žádost',

    // 422
    SymfonyResponse::$statusTexts[422] => 'Nezpracovatelná entita',

    // 422
    'The Given Data Was Invalid' => 'Odeslané údaje obsahují chyby',

    // 423
    SymfonyResponse::$statusTexts[423] => 'Uzamčeno',

    // 424
    SymfonyResponse::$statusTexts[424] => 'Selhání závislosti',

    // 425
    SymfonyResponse::$statusTexts[425] => 'Přiliš brzy',

    // 426
    SymfonyResponse::$statusTexts[426] => 'Vyžaduje se upgrade',

    // 428
    SymfonyResponse::$statusTexts[428] => 'Je nutná podmínka',

    // 429
    SymfonyResponse::$statusTexts[429] => 'Omlouváme se, ale zadáváte příliš mnoho požadavků na naše servery',

    // 431
    SymfonyResponse::$statusTexts[431] => 'Hlavičky požadavku jsou přiliš velké',

    // 451
    SymfonyResponse::$statusTexts[451] => 'Nedostupné z právních důvodů',

    // 500
    SymfonyResponse::$statusTexts[500] => 'Ups, něco se pokazilo na našich serverech',

    // 501
    SymfonyResponse::$statusTexts[501] => 'Není implementováno',

    // 502
    SymfonyResponse::$statusTexts[502] => 'Chybná brána',

    // 503
    SymfonyResponse::$statusTexts[503] => 'Omlouváme se, provádíme údržbu, zkontrolujte to prosím později',

    // 504
    SymfonyResponse::$statusTexts[504] => 'Časový limit brány vypršel',

    // 505
    SymfonyResponse::$statusTexts[505] => 'Verze HTTP není podporována',

    // 506
    SymfonyResponse::$statusTexts[506] => 'Varianta tak= vyjednávat',

    // 507
    SymfonyResponse::$statusTexts[507] => 'Nedostatečné úložiště',

    // 508
    SymfonyResponse::$statusTexts[508] => 'Zjištěna smyčka',

    // 510
    SymfonyResponse::$statusTexts[510] => 'Nerozšířeno',

    // 511
    SymfonyResponse::$statusTexts[511] => 'Je vyžadováno ověření v síti',

    // Fallback
    Handler::ERROR_MESSAGE_UNEXPECTED_ERROR => 'Ups, něco se pokazilo',

    // 400
    Tomchochola\Laratchi\Http\Middleware\MustBeStatefulMiddleware::ERROR_MESSAGE => 'Požadavek musí podporovat cookies',

    // 400
    Tomchochola\Laratchi\Http\Middleware\MustBeStatelessMiddleware::ERROR_MESSAGE => 'Požadavek musí být bezestavový',

    // 427
    Tomchochola\Laratchi\Exceptions\MustBeGuestHttpException::ERROR_MESSAGE => 'Musíte se nejdříve odhlásit',
];
