<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AnalyticsTest extends WebTestCase
{
    use SitePages;

    public function testTrackerIsAbsentOutsideProduction(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('_paq', (string) $client->getResponse()->getContent());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('legalPages')]
    public function testTrackerIsAbsentOnTheLegalPagesToo(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('_paq', (string) $client->getResponse()->getContent());
    }
}
