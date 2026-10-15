<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Profil, the curriculum vitae at the start address, is driven by
 * config/content. A typo in one of those files degrades to an empty list
 * rather than an error, so what breaks silently is a station, a competence
 * or a passion that no longer reaches the page—or a trade whose marker no
 * longer says it is one.
 */
final class ProfilTest extends WebTestCase
{
    /**
     * The marker is what the stylesheet colors a trade's station by; a
     * station that loses it turns gray without anyone noticing.
     */
    public function testEveryCareerStationReachesThePageWithItsMarker(): void
    {
        $milestones = $this->content('milestones');
        self::assertNotEmpty($milestones);

        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $stations = $crawler->filter('#werdegang ol > li');
        self::assertCount(count($milestones), $stations);

        foreach ($milestones as $index => $milestone) {
            $station = $stations->eq($index);
            self::assertStringContainsString($milestone['company'], $station->filter('h3')->text());

            self::assertSame($milestone['marker'] ?? null, $station->attr('data-marker'), $milestone['company']);

            foreach ($milestone['roles'] ?? [$milestone] as $role) {
                self::assertStringContainsString($role['position'], $station->text(), $milestone['company']);
            }

            if (isset($milestone['url'])) {
                $homepage = $station->filter(sprintf('a[href="%s"]', $milestone['url']));
                self::assertCount(1, $homepage, $milestone['company']);
                self::assertSame('_blank', $homepage->attr('target'));
                self::assertStringContainsString($milestone['company'], $homepage->text());
                self::assertStringContainsString('öffnet in neuem Tab', $homepage->text());
            }
        }
    }

    /**
     * Chefkoch is one employer with two roles: one company line, both
     * periods. Two company lines would read as two jobs.
     */
    public function testSeveralRolesStandUnderOneEmployer(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        foreach ($this->content('milestones') as $milestone) {
            if (!isset($milestone['roles'])) {
                continue;
            }

            $headings = $crawler->filter('#werdegang h3')->reduce(
                static fn ($node): bool => trim($node->text()) === $milestone['company'],
            );
            self::assertCount(1, $headings, $milestone['company']);

            foreach ($milestone['roles'] as $role) {
                self::assertSelectorTextContains('#werdegang', $role['years']);
            }
        }
    }

    public function testEveryCompetenceReachesThePage(): void
    {
        $skills = $this->content('skills');
        self::assertNotEmpty($skills);

        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $section = $crawler->filter('#kompetenzen')->text();

        foreach ($skills as $group) {
            self::assertStringContainsString($group['group'], $section);
            foreach ($group['entries'] as $entry) {
                self::assertStringContainsString($entry['name'], $section);
            }
        }
    }

    public function testEveryPassionReachesThePageAndLeadsToWhatCameOfItInANewTab(): void
    {
        $hobbies = $this->content('hobbies');
        self::assertNotEmpty($hobbies);

        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        foreach ($hobbies as $hobby) {
            self::assertSelectorTextContains('#leidenschaft', $hobby['title']);

            if (isset($hobby['url'])) {
                $link = $crawler->filter(sprintf('#leidenschaft a[href="%s"]', $hobby['url']));
                self::assertCount(1, $link, $hobby['title']);
                self::assertStringContainsString($hobby['urlLabel'], $link->text());
                self::assertSame('_blank', $link->attr('target'));
                self::assertStringContainsString('öffnet in neuem Tab', $link->text());
            }
        }
    }

    public function testStructuredDataCarriesTheSkillsFromTheContentFiles(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $body = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('"@type": "Person"', $body);

        // Terms that exist in skills.json and hobbies.json alone: a term the
        // career carries as well stays on the page when a content file
        // degrades to an empty list.
        self::assertStringContainsString('CAN-Bus', $body);
        self::assertStringContainsString('Sim Racing', $body);
    }

    /**
     * Beside each offer of the document stands the way to a message; the
     * form itself lives on Kontakt.
     */
    public function testTheDocumentOpensInANewTabAndTheMessageLeadsToKontakt(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $router = static::getContainer()->get('router');
        $documents = $crawler->filter(sprintf('main a[href="%s"]', $router->generate('app_curriculum_vitae_pdf')));
        self::assertNotEmpty($documents);
        foreach ($documents as $document) {
            self::assertSame('_blank', $document->getAttribute('target'));
            self::assertStringContainsString('öffnet in neuem Tab', $document->textContent);
        }

        $messages = $crawler->filter(sprintf('main a[href="%s"]', $router->generate('app_contact_page')));
        self::assertCount($documents->count(), $messages);
        self::assertCount(0, $crawler->filter('main form'));
    }

    public function testNoSectionCarriesANumber(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $numbers = $crawler->filter('main *')->reduce(
            static fn ($node): bool => preg_match('/^\s*0\d\s*$/', $node->text()) === 1,
        );
        self::assertCount(0, $numbers);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function content(string $name): array
    {
        return json_decode((string) file_get_contents(sprintf('%s/../../config/content/%s.json', __DIR__, $name)), true);
    }
}
