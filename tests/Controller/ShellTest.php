<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShellTest extends WebTestCase
{
    use SitePages;

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testTheHeaderHoldsTheLogoAloneLinkingHome(string $path): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        $links = $crawler->filter('header a')->each(static fn ($link): string => (string) $link->attr('href'));
        self::assertSame(['/'], $links);
        self::assertCount(0, $crawler->filter('header nav'));
    }

    // Marcel hands the link to the printed curriculum vitae out himself.
    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testNoPageLinksToTheDocument(string $path): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        self::assertCount(0, $crawler->filter('a[href^="/lebenslauf"]'));
    }
}
