<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class FooterTest extends WebTestCase
{
    use AssertsNewTabLinks;
    use SitePages;

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testTheFooterCarriesTheLegalLinksAndTheProfiles(string $path): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        $links = $crawler->filter('footer a')->each(static fn ($link): string => (string) $link->attr('href'));
        self::assertEqualsCanonicalizing([
            '/datenschutz',
            '/impressum',
            'https://github.com/marcelkraus',
            'https://www.linkedin.com/in/kraus-marcel',
        ], $links);
    }

    public function testEveryProfileOpensInANewTab(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $profiles = $crawler->filter('footer a[href^="https://"]');
        self::assertCount(2, $profiles);

        foreach ($profiles as $profile) {
            self::assertOpensInNewTab(new Crawler($profile));
        }
    }
}
