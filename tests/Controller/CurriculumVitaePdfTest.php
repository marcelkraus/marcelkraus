<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

    public function testTheDocumentIsShownInTheBrowserAndKeepsItsFileName(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        $disposition = (string) $client->getResponse()->headers->get('Content-Disposition');
        self::assertStringStartsWith('inline', $disposition);
        self::assertStringContainsString('Lebenslauf-Marcel-Kraus.pdf', $disposition);
    }

    public function testTheDocumentIsKeptOutOfSearchIndexes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');

        self::assertSame('noindex', $client->getResponse()->headers->get('X-Robots-Tag'));
    }

    // One longer station description pushes the layout to a third page.
    #[DataProvider('copies')]
    public function testTheDocumentIsExactlyTwoPages(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        $pages = preg_match_all('#/Type\s*/Page[^s]#', (string) $client->getResponse()->getContent());

        self::assertSame(2, $pages);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function copies(): iterable
    {
        yield 'public' => ['/lebenslauf'];
        yield 'with contact' => ['/lebenslauf?kontakt'];
    }

    // Only the logo paints purple, and it is the one drawing with both sibling colors.
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

    public function testThePublicDocumentLeavesPhoneAndEmailToTheHomepage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf');
        $pdf = (string) $client->getResponse()->getContent();

        self::assertFalse(self::shows($pdf, 'TELEFON'));
        self::assertFalse(self::shows($pdf, 'E-MAIL'));
    }

    public function testTheContactParameterAddsPhoneAndEmail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lebenslauf?kontakt');
        $pdf = (string) $client->getResponse()->getContent();

        self::assertTrue(self::shows($pdf, 'TELEFON'));
        self::assertTrue(self::shows($pdf, 'E-MAIL'));
    }

    public function testThePageLinksToTheDocument(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertCount(2, $crawler->filter('a[href="/lebenslauf"]'));
    }

    // mPDF writes text as UTF-16BE literals and the labels in capitals.
    private static function shows(string $pdf, string $text): bool
    {
        preg_match_all('#stream\r?\n(.*?)\r?\nendstream#s', $pdf, $matches);
        $needle = mb_convert_encoding($text, 'UTF-16BE', 'UTF-8');

        foreach ($matches[1] as $raw) {
            if (str_contains(@gzuncompress($raw) ?: $raw, $needle)) {
                return true;
            }
        }

        return false;
    }
}
