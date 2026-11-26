<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class HomepageTest extends WebTestCase
{
    use AssertsNewTabLinks;

    public function testEveryBrandStandsAsATowerLinkingToItsDomainInANewTab(): void
    {
        $brands = self::content('brands');
        self::assertCount(2, $brands);

        $crawler = $this->homepage();
        self::assertResponseIsSuccessful();

        foreach ($brands as $brand) {
            $link = $crawler->filter(sprintf('main a[href="%s"]', $brand['url']));
            self::assertCount(1, $link, $brand['name']);
            self::assertStringContainsString($brand['domain'], $link->text());
            self::assertOpensInNewTab($link);

            $tower = $link->closest('article');
            self::assertNotNull($tower, $brand['name']);
            self::assertStringContainsString($brand['name'], $tower->filter('h2')->text());
            foreach ($brand['claim'] as $line) {
                self::assertStringContainsString($line, $tower->text());
            }
        }
    }

    public function testEveryWorkStandsWithItsKindItsSentenceAndADestinationInANewTab(): void
    {
        $works = self::content('works');
        self::assertNotEmpty($works);

        $crawler = $this->homepage();
        self::assertResponseIsSuccessful();

        foreach ($works as $work) {
            $link = $crawler->filter(sprintf('#works a[href="%s"]', $work['url']));
            self::assertCount(1, $link, $work['name']);
            self::assertStringContainsString($work['urlLabel'], $link->text());
            self::assertOpensInNewTab($link);

            $cell = $link->closest('article');
            self::assertNotNull($cell, $work['name']);
            self::assertStringContainsString($work['name'], $cell->filter('h3')->text());
            self::assertStringContainsString($work['kind'], $cell->text());
            self::assertStringContainsString($work['description'], $cell->text());
        }
    }

    public function testTheClosingCellSendsTheReaderToTheContactSectionOfKrausgebaut(): void
    {
        $crawler = $this->homepage();

        $link = $crawler->filter('#works a')->reduce(
            static fn ($candidate): bool => str_contains($candidate->text(), 'Nachricht schreiben'),
        );
        self::assertCount(1, $link);
        self::assertSame('https://www.krausgebaut.de/#kontakt', $link->attr('href'));
        self::assertOpensInNewTab($link);
    }

    public function testNoScreenshotIsFetchedFromElsewhere(): void
    {
        $crawler = $this->homepage();

        $sources = $crawler->filter('#works img')->each(static fn ($image): string => (string) $image->attr('src'));
        self::assertNotEmpty($sources);
        foreach ($sources as $source) {
            self::assertStringStartsWith('/images/works/', $source);
            self::assertFileExists(__DIR__ . '/../../public' . strtok($source, '?'));
        }
    }

    public function testThePersonMarkupNamesTheProfilesAndTheTwoBrands(): void
    {
        $crawler = $this->homepage();

        $person = json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('Person', $person['@type']);
        self::assertSame('Marcel Kraus', $person['name']);
        self::assertSame(['https://github.com/marcelkraus', 'https://www.linkedin.com/in/kraus-marcel'], $person['sameAs']);
        self::assertSame(
            ['https://www.krausgebaut.de', 'https://www.krausgedruckt.de'],
            array_map(static fn (array $brand): string => $brand['url'], $person['brand']),
        );
        self::assertArrayNotHasKey('knowsAbout', $person);
    }

    private function homepage(): Crawler
    {
        return static::createClient()->request('GET', '/');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function content(string $name): array
    {
        return json_decode((string) file_get_contents(__DIR__ . "/../../config/content/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);
    }
}
