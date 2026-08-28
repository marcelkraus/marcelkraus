<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The printed curriculum vitae is generated from the same content files the
 * page renders, so the failure worth guarding against is not a wrong pixel –
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
     * Inline, so the document opens in the browser – but the file name still
     * travels with it, so saving it produces something recognisable rather
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
     * and nothing else would notice.
     */
    public function testTheDocumentIsAtMostTwoPages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        $pages = preg_match_all('#/Type\s*/Page[^s]#', (string) $client->getResponse()->getContent());

        self::assertGreaterThan(0, $pages, 'No page objects found – the parser, not the document, is wrong.');
        self::assertLessThanOrEqual(2, $pages);
    }

    /**
     * Both buttons that lead to the document, because a document nobody can
     * reach is the same as no document.
     */
    public function testThePageLinksToTheDocumentTwice(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertCount(2, $crawler->filter('a[href="/lebenslauf"]'));
    }
}
