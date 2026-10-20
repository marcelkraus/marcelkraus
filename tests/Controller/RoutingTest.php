<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RoutingTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function successfulPaths(): iterable
    {
        yield 'homepage' => ['/'];
        yield 'portfolio' => ['/portfolio'];
        yield 'contact' => ['/kontakt'];
        yield 'imprint' => ['/impressum'];
        yield 'privacy' => ['/datenschutz'];
        yield 'robots' => ['/robots.txt'];
        yield 'sitemap' => ['/sitemap.xml'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('successfulPaths')]
    public function testPathAnswers(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    public function testLegalPagesAreNotIndexed(): void
    {
        $client = static::createClient();

        foreach (['/impressum', '/datenschutz'] as $path) {
            $client->request('GET', $path);

            self::assertSelectorExists('meta[name="robots"][content="noindex,follow"]');
        }
    }

    public function testTheThreePagesAreIndexed(): void
    {
        $client = static::createClient();

        foreach (['/', '/portfolio', '/kontakt'] as $path) {
            $client->request('GET', $path);

            self::assertSelectorExists('meta[name="robots"][content="index,follow"]');
        }
    }

    // Outside dev the route must not exist, or it would be indexed as a fourth page.
    public function testTheSharingImagePageIsNotServedOutsideDev(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sharing-image');

        self::assertResponseStatusCodeSame(404);
    }

    public function testContactRoutesRedirectAndKeepTheAddressOutOfTheDocument(): void
    {
        $client = static::createClient();

        $client->request('GET', '/kontakt-per-email');

        self::assertResponseRedirects();
    }

    public function testTheRetiredEnglishPathsRedirectToTheHomepage(): void
    {
        $client = static::createClient();

        // '/en/' takes two hops, so the chain is followed.
        foreach (['/en', '/en/imprint'] as $path) {
            $client->request('GET', $path);

            self::assertResponseRedirects('/', 301, $path);
        }

        $client->followRedirects();
        $client->request('GET', '/en/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#werdegang');
    }

    public function testTheMailAddressNeverAppearsInTheMarkup(): void
    {
        $client = static::createClient();

        // The legal pages are the ones that show the address.
        foreach (['/', '/impressum', '/datenschutz'] as $path) {
            $client->request('GET', $path);
            $body = (string) $client->getResponse()->getContent();

            self::assertStringNotContainsString('mail@marcelkraus.de', $body, $path);
            self::assertStringNotContainsString('mailto:', $body, $path);
        }
    }

    // Both halves: the readable disclosure and the decoy only the markup carries.
    public function testTheLegalPagesSpellTheMailboxOutBehindADecoy(): void
    {
        $client = static::createClient();

        foreach (['/impressum', '/datenschutz'] as $path) {
            $client->request('GET', $path);
            $body = (string) $client->getResponse()->getContent();

            self::assertStringContainsString(
                'mail+legal@<span style="display:none" aria-hidden="true">nospam.</span>marcelkraus.de',
                $body,
                $path,
            );
            self::assertStringNotContainsString('mail(at)', $body, $path);
        }
    }

    public function testSitemapNamesExactlyProfilPortfolioAndKontakt(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        $xml = simplexml_load_string((string) $client->getResponse()->getContent());
        self::assertNotFalse($xml);

        $paths = [];
        foreach ($xml->url as $url) {
            $paths[] = (string) parse_url((string) $url->loc, PHP_URL_PATH);
        }

        self::assertSame(['/', '/portfolio', '/kontakt'], $paths);
    }

    public function testRobotsKeepsTheRedirectRoutesOutOfCrawlerCorpora(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        $body = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Disallow: /kontakt-per-email', $body);
    }

    // The site belongs to private life, so it offers no WhatsApp; a stale link must not lead to the business account.
    public function testTheSiteOffersNoWhatsApp(): void
    {
        $client = static::createClient();
        $client->request('GET', '/kontakt-per-whats-app');

        self::assertResponseStatusCodeSame(404);
    }
}
