<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The homepage is driven entirely by config/content. These tests pin that the
 * files are readable and reach the page — a typo in one of them degrades to an
 * empty list rather than an error, which is exactly the failure that goes
 * unnoticed.
 */
final class ContentTest extends WebTestCase
{
    public function testEverySectionAnchorIsPresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        foreach (['werdegang', 'kompetenzen', 'leidenschaft', 'kontakt', 'selbststaendigkeit'] as $anchor) {
            self::assertSelectorExists(sprintf('#%s', $anchor));
        }
    }

    public function testNavigationLabelsMatchTheHeadingsTheyPointAt(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $links = $crawler->filter('header nav[aria-label="Hauptnavigation"] a');
        self::assertCount(4, $links);

        // Checked against the section the link points at, not against the
        // document: "Kontakt" also stands in the header button and in the
        // footer, so a document-wide search would pass even if the eyebrow of
        // the section said something else entirely.
        foreach ($links as $link) {
            $label = trim((string) $link->textContent);
            $anchor = substr((string) $link->getAttribute('href'), strpos((string) $link->getAttribute('href'), '#') + 1);

            self::assertSelectorTextContains(sprintf('#%s p', $anchor), $label);
        }
    }

    public function testMilestonesAndBrandsAreRendered(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertSelectorTextContains('#werdegang', 'Chefkoch GmbH');
        self::assertSelectorTextContains('#werdegang', 'Arbeiter-Samariter-Bund Deutschland e.V.');
        self::assertSelectorTextContains('#selbststaendigkeit', 'krausgebaut');
        self::assertSelectorTextContains('#selbststaendigkeit', 'krausgedruckt');
    }

    public function testStructuredDataCarriesTheSkillsFromTheContentFiles(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $body = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('"@type": "Person"', $body);

        // Deliberately not a term the career carries as well: 'Swift' also
        // sits in a milestone tag, so it stayed in the document while
        // skills.json degraded to an empty list — which is the one failure
        // this test is here to catch. 'Objective-C' and 'Anforderungsanalyse'
        // exist in skills.json alone, and the visible section is asserted
        // next to the structured data.
        self::assertStringContainsString('Objective-C', $body);
        self::assertStringContainsString('Sim Racing', $body);
        self::assertSelectorTextContains('#kompetenzen', 'Anforderungsanalyse');
    }
}
