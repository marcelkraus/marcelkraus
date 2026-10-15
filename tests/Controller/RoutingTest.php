<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Every route answers, the legal pages stay out of the index and the two
 * contact routes redirect instead of carrying the address in a document.
 */
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

    /**
     * The page the sharing image is photographed from lives in dev alone.
     * Outside it the route does not exist, so it can never be indexed as a
     * fourth page that carries nothing but the cover card.
     */
    public function testTheSharingImagePageIsNotServedOutsideDev(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sharing-image');

        self::assertResponseStatusCodeSame(404);
    }

    public function testContactRoutesRedirectAndKeepTheAddressOutOfTheDocument(): void
    {
        $client = static::createClient();

        foreach (['/kontakt-per-email', '/kontakt-per-whats-app'] as $path) {
            $client->request('GET', $path);

            self::assertResponseRedirects();
        }
    }

    public function testTheRetiredEnglishPathsRedirectToTheHomepage(): void
    {
        $client = static::createClient();

        // '/en/' takes two hops: Symfony strips the trailing slash first, and
        // the redirect below answers the result. What matters is where the
        // visitor ends up, so the chain is followed rather than the first step
        // asserted.
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

        // The legal pages are the ones that actually show the address, so
        // checking the homepage alone leaves the risk untested.
        foreach (['/', '/impressum', '/datenschutz'] as $path) {
            $client->request('GET', $path);
            $body = (string) $client->getResponse()->getContent();

            self::assertStringNotContainsString('mail@marcelkraus.de', $body, $path);
            self::assertStringNotContainsString('mailto:', $body, $path);
        }
    }

    /**
     * The mailbox is a mandatory disclosure, so it has to be readable—and it
     * is hidden from a harvester by a decoy that only the markup carries. Both
     * halves are asserted: drop the decoy and the obfuscation is gone, drop
     * the plain address and the disclosure is.
     */
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

    /**
     * Exactly the three pages worth finding: a legal page in the sitemap
     * contradicts its own `noindex`, and a page missing from it is found late.
     */
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
        self::assertStringContainsString('Disallow: /kontakt-per-whats-app', $body);
    }
}
