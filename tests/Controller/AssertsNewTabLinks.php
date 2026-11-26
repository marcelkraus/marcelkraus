<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;

trait AssertsNewTabLinks
{
    private static function assertOpensInNewTab(Crawler $link): void
    {
        self::assertSame('_blank', $link->attr('target'));
        self::assertStringContainsString('noopener', (string) $link->attr('rel'));
        self::assertStringContainsString('öffnet in neuem Tab', $link->text());
    }
}
