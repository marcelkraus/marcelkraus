<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShellTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'Profil' => ['/'];
        yield 'Portfolio' => ['/portfolio'];
        yield 'Kontakt' => ['/kontakt'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testEveryPageCarriesTheThreeTabsWithItsOwnActive(string $path): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        $tabs = $crawler->filter('nav[aria-label="Bereiche"] a');
        self::assertSame(['/', '/portfolio', '/kontakt'], $tabs->each(static fn ($link): string => (string) $link->attr('href')));

        $active = $crawler->filter('nav[aria-label="Bereiche"] a[aria-current="page"]');
        self::assertCount(1, $active);
        self::assertSame($path, $active->attr('href'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testEveryPageOpensWithTheCoverCard(string $path): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        self::assertCount(1, $crawler->filter('main h1'));
        self::assertCount(1, $crawler->filter('main img[src*="portrait"]'));
    }

    public function testTheLegalPagesCarryTheTabsWithNoneActiveAndNoCoverCard(): void
    {
        $client = static::createClient();

        foreach (['/impressum', '/datenschutz'] as $path) {
            $crawler = $client->request('GET', $path);

            self::assertCount(3, $crawler->filter('nav[aria-label="Bereiche"] a'), $path);
            self::assertCount(0, $crawler->filter('nav[aria-label="Bereiche"] a[aria-current]'), $path);
            self::assertCount(0, $crawler->filter('main img[src*="portrait"]'), $path);
        }
    }

    public function testTheCoverCardPointsAtTheContactPageExceptOnIt(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/portfolio');
        $message = $crawler->filter('main a[href="/kontakt"]');
        self::assertCount(1, $message);
        self::assertNull($message->attr('target'));

        $crawler = $client->request('GET', '/kontakt');
        self::assertCount(0, $crawler->filter('main a[href="/kontakt"]'));
    }

    public function testTheDocumentLinkOpensANewTabAndAnnouncesIt(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $document = $crawler->filter('main a[href="/lebenslauf"]')->first();
        self::assertSame('_blank', $document->attr('target'));
        self::assertStringContainsString('noopener', (string) $document->attr('rel'));
        self::assertStringContainsString('öffnet in neuem Tab', $document->text());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testTheFooterCarriesTheLegalLinksAndNothingElse(string $path): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        $links = $crawler->filter('footer a')->each(static fn ($link): string => (string) $link->attr('href'));
        self::assertSame(['/impressum', '/datenschutz'], $links);
    }
}
