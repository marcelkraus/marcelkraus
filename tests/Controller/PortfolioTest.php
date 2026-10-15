<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PortfolioTest extends WebTestCase
{
    public function testEveryBrandStandsAsATowerLinkingToItsDomainInANewTab(): void
    {
        $brands = json_decode((string) file_get_contents(__DIR__ . '/../../config/content/brands.json'), true);
        self::assertCount(2, $brands);

        $client = static::createClient();
        $crawler = $client->request('GET', '/portfolio');
        self::assertResponseIsSuccessful();

        foreach ($brands as $brand) {
            $link = $crawler->filter(sprintf('main a[href="%s"]', $brand['url']));
            self::assertCount(1, $link, $brand['name']);
            self::assertStringContainsString($brand['domain'], $link->text());
            self::assertSame('_blank', $link->attr('target'));
            self::assertStringContainsString('noopener', (string) $link->attr('rel'));
            self::assertStringContainsString('öffnet in neuem Tab', $link->text());

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
        $works = json_decode((string) file_get_contents(__DIR__ . '/../../config/content/works.json'), true);
        self::assertNotEmpty($works);

        $client = static::createClient();
        $crawler = $client->request('GET', '/portfolio');
        self::assertResponseIsSuccessful();

        foreach ($works as $work) {
            $link = $crawler->filter(sprintf('#works a[href="%s"]', $work['url']));
            self::assertCount(1, $link, $work['name']);
            self::assertStringContainsString($work['urlLabel'], $link->text());
            self::assertSame('_blank', $link->attr('target'));
            self::assertStringContainsString('noopener', (string) $link->attr('rel'));
            self::assertStringContainsString('öffnet in neuem Tab', $link->text());

            $cell = $link->closest('article');
            self::assertNotNull($cell, $work['name']);
            self::assertStringContainsString($work['name'], $cell->filter('h3')->text());
            self::assertStringContainsString($work['kind'], $cell->text());
            self::assertStringContainsString($work['description'], $cell->text());
        }
    }

    public function testNoScreenshotIsFetchedFromElsewhere(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/portfolio');

        $sources = $crawler->filter('#works img')->each(static fn ($image): string => (string) $image->attr('src'));
        self::assertNotEmpty($sources);
        foreach ($sources as $source) {
            self::assertStringStartsWith('/images/works/', $source);
            self::assertFileExists(__DIR__ . '/../../public' . $source);
        }
    }

    public function testTheClosingCellCarriesTheAddressAndTheDocumentInANewTab(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/portfolio');

        $closing = $crawler->filter('#works address')->closest('div');
        self::assertNotNull($closing);

        $path = static::getContainer()->get('router')->generate('app_curriculum_vitae_pdf');
        $document = $closing->filter(sprintf('a[href="%s"]', $path));
        self::assertCount(1, $document);
        self::assertSame('_blank', $document->attr('target'));
        self::assertStringContainsString('öffnet in neuem Tab', $document->text());
    }
}
