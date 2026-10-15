<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The printed curriculum vitae is generated from the same content files the
 * page renders, so the failure worth guarding against is not a wrong pixel—
 * it is the document silently losing a station, gaining a third page, or
 * turning up in a search index.
 */
final class CurriculumVitaePdfTest extends WebTestCase
{
    public function testTheRouteAnswersWithAPdf(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');
        $response = $client->getResponse();

        self::assertResponseIsSuccessful();
        self::assertSame('application/pdf', $response->headers->get('Content-Type'));
        self::assertStringStartsWith('%PDF-', (string) $response->getContent());
    }

    /**
     * Inline, so the document opens in the browser—but the file name still
     * travels with it, so saving it produces something recognizable rather
     * than "lebenslauf".
     */
    public function testTheDocumentIsShownInTheBrowserAndKeepsItsFileName(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        $disposition = (string) $client->getResponse()->headers->get('Content-Disposition');
        self::assertStringStartsWith('inline', $disposition);
        self::assertStringContainsString('Lebenslauf-Marcel-Kraus.pdf', $disposition);
    }

    /**
     * The file duplicates the homepage and carries the postal address, so the
     * indexed truth stays the page. Asserted as a header rather than as a
     * robots.txt rule: a disallowed path is never fetched, and a crawler that
     * never fetches never reads the header telling it not to index.
     */
    public function testTheDocumentIsKeptOutOfSearchIndexes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        self::assertSame('noindex', $client->getResponse()->headers->get('X-Robots-Tag'));
    }

    /**
     * Two pages is a requirement, not a preference. The layout sits close
     * enough to the limit that a longer station description pushes it over,
     * and nothing else would notice. One page would mean a section went
     * missing; none, that the parser rather than the document is wrong.
     */
    public function testTheDocumentIsExactlyTwoPages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        $pages = preg_match_all('#/Type\s*/Page[^s]#', (string) $client->getResponse()->getContent());

        self::assertSame(2, $pages);
    }

    /**
     * The purple belongs to the logo and nowhere else in the document. The
     * logo is the one drawing that also carries the colors of both sibling
     * brands, so any stream painting in purple without them is the byline, a
     * position or a section marker turning purple again.
     */
    public function testPurpleAppearsInTheLogoAlone(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        preg_match_all('#stream\r?\n(.*?)\r?\nendstream#s', (string) $client->getResponse()->getContent(), $matches);
        $streams = array_map(static fn (string $raw): string => @gzuncompress($raw) ?: $raw, $matches[1]);

        $purple = '0.510 0.000 0.859 rg';
        $petrol = '0.000 0.373 0.471 rg';
        $papaya = '0.961 0.286 0.000 rg';
        $paintedInPurple = array_filter($streams, static fn (string $stream): bool => str_contains($stream, $purple));

        $outsideTheLogo = array_filter(
            $paintedInPurple,
            static fn (string $stream): bool => !str_contains($stream, $petrol) || !str_contains($stream, $papaya),
        );

        self::assertNotEmpty($paintedInPurple, 'No purple found at all—the logo is missing, or the parser is wrong.');
        self::assertCount(0, $outsideTheLogo, 'Purple outside the logo.');
    }

    /**
     * A document nobody can reach is the same as no document. Profil offers
     * it twice: in the cover card and in the closing card.
     */
    public function testThePageLinksToTheDocument(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertCount(2, $crawler->filter('a[href="/lebenslauf"]'));
    }
}
