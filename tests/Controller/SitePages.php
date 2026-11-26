<?php

declare(strict_types=1);

namespace App\Tests\Controller;

trait SitePages
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function legalPages(): iterable
    {
        yield 'imprint' => ['/impressum'];
        yield 'privacy' => ['/datenschutz'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'homepage' => ['/'];
        yield from self::legalPages();
    }
}
