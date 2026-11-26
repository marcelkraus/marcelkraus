<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RoutingTest extends WebTestCase
{
    use SitePages;

    /**
     * @return iterable<string, array{string}>
     */
    public static function successfulPaths(): iterable
    {
        yield from self::pages();
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

    #[\PHPUnit\Framework\Attributes\DataProvider('legalPages')]
    public function testLegalPagesAreNotIndexed(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertSelectorExists('meta[name="robots"][content="noindex,follow"]');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('legalPages')]
    public function testTheLegalPagesNameNoContactForm(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);
        self::assertSelectorTextNotContains('main', 'Kontaktformular');
    }

    public function testTheHomepageIsIndexed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertSelectorExists('meta[name="robots"][content="index,follow"]');
    }

    public function testPortfolioAnswersNotFoundWithoutARedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio');

        self::assertResponseStatusCodeSame(404);
    }

    // Outside dev the route must not exist, or it would be indexed as a second page.
    public function testTheSharingImagePageIsNotServedOutsideDev(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sharing-image');

        self::assertResponseStatusCodeSame(404);
    }

    public function testTheContactRoutesAreGoneWithoutARedirect(): void
    {
        $client = static::createClient();

        foreach ([['GET', '/kontakt'], ['POST', '/kontakt'], ['GET', '/kontakt-per-email']] as [$method, $path]) {
            $client->request($method, $path);

            self::assertResponseStatusCodeSame(404, "{$method} {$path}");
        }
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
        self::assertSelectorExists('#works');
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
    #[\PHPUnit\Framework\Attributes\DataProvider('legalPages')]
    public function testTheLegalPagesSpellTheMailboxOutBehindADecoy(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);
        $body = (string) $client->getResponse()->getContent();

        self::assertStringContainsString(
            'mail+legal@<span style="display:none" aria-hidden="true">nospam.</span>marcelkraus.de',
            $body,
            $path,
        );
        self::assertStringNotContainsString('mail(at)', $body, $path);
    }

    public function testSitemapNamesTheHomepageAlone(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        $xml = simplexml_load_string((string) $client->getResponse()->getContent());
        self::assertNotFalse($xml);

        $paths = [];
        foreach ($xml->url as $url) {
            $paths[] = (string) parse_url((string) $url->loc, PHP_URL_PATH);
        }

        self::assertSame(['/'], $paths);
    }

    public function testRobotsDisallowsNothing(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        $body = (string) $client->getResponse()->getContent();
        self::assertMatchesRegularExpression('/^Disallow:$/m', $body);
        self::assertDoesNotMatchRegularExpression('/^Disallow: *\S/m', $body);
    }

    // The site takes no inquiries, so it offers no WhatsApp; a stale link must not lead to the business account.
    public function testTheSiteOffersNoWhatsApp(): void
    {
        $client = static::createClient();
        $client->request('GET', '/kontakt-per-whats-app');

        self::assertResponseStatusCodeSame(404);
    }
}
