<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The homepage is driven entirely by config/content. These tests pin that the
 * files are readable and reach the page – a typo in one of them degrades to an
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
        // skills.json degraded to an empty list – which is the one failure
        // this test is here to catch. 'CAN-Bus' and 'Anforderungsanalyse'
        // exist in skills.json alone, and the visible section is asserted
        // next to the structured data.
        self::assertStringContainsString('CAN-Bus', $body);
        self::assertStringContainsString('Sim Racing', $body);
        self::assertSelectorTextContains('#kompetenzen', 'Anforderungsanalyse');
    }

    /**
     * Every station has to reach the page, because the curriculum vitae and
     * the PDF fassung are held content-identical: a station that silently
     * fails to render here would be missing from both.
     */
    public function testEveryCareerStationReachesThePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        foreach ([
            'Adolf-Kolping-Berufskolleg',
            'Arbeiter-Samariter-Bund Deutschland e.V.',
            'Chefkoch GmbH',
            'Jurassic Jeep',
            'krausgebaut',
            'krausgedruckt',
        ] as $company) {
            self::assertSelectorTextContains('#werdegang', $company);
        }

        // The vocational school carries no description of its own and would
        // disappear unnoticed if the condensed branch broke.
        self::assertSelectorTextContains('#werdegang', 'Informationstechnischer Assistent');
    }

    /**
     * Chefkoch is one employer with two roles. Both role labels and both
     * periods have to render, otherwise the nesting has swallowed one of them
     * – which is invisible in a passing "Chefkoch GmbH" assertion.
     */
    public function testChefkochRendersBothRolesUnderOneEmployer(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertSelectorTextContains('#werdegang', 'Senior iOS-Entwickler');
        self::assertSelectorTextContains('#werdegang', 'PHP-Entwickler');
        self::assertSelectorTextContains('#werdegang', '05/2017 – 11/2026');
        self::assertSelectorTextContains('#werdegang', '07/2016 – 04/2017');

        // One company line, not two: the whole point of the nesting.
        self::assertSame(1, $crawler->filter('#werdegang')->filter('p')->reduce(
            static fn ($node): bool => trim((string) $node->text()) === 'Chefkoch GmbH',
        )->count());
    }

    /**
     * The availability is computed from the notice period rather than written
     * out. Asserted as a shape, not as a value: the value moves with the
     * calendar, and a test pinned to one month would fail on its own the next
     * time the boundary passes.
     */
    public function testAvailabilityIsStatedAsAMonth(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $chips = $crawler->filter('dl div')->reduce(
            static fn ($node): bool => trim((string) $node->filter('dt')->text()) === 'Verfügbarkeit',
        );

        self::assertCount(1, $chips);
        self::assertMatchesRegularExpression(
            '/^(Ab \w+ \d{4}|Sofort)$/u',
            trim((string) $chips->filter('dd')->text()),
        );
    }
}
