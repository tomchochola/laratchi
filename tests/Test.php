<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function test(): void
    {
        $this::expectNotToPerformAssertions();
    }
}
