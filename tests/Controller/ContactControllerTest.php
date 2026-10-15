<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final class ContactControllerTest extends WebTestCase
{
    use MailerAssertionsTrait;

    /**
     * Exactly one contact form on the site, and it stands on Kontakt.
     */
    public function testTheContactPageCarriesTheOnlyForm(): void
    {
        $client = static::createClient();

        $client->request('GET', '/kontakt');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[action="/kontakt"]');

        foreach (['/', '/portfolio'] as $path) {
            $client->request('GET', $path);
            self::assertSelectorNotExists('form', $path);
        }
    }

    /**
     * The channels stand beside the form, so a visitor picks the one that
     * suits. E-mail and WhatsApp are redirect routes; the address of either
     * never reaches the markup.
     */
    public function testTheContactPageOffersTheChannelsBesideTheForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/kontakt');

        self::assertSelectorTextContains('.contact', 'Bonner Str. 88 · 50374 Erftstadt');
        self::assertSelectorTextContains('.contact', 'In der Regel innerhalb eines Werktags');
        self::assertSelectorExists('.contact a[href="/kontakt-per-email"]');
        self::assertSelectorExists('.contact a[href="/kontakt-per-whats-app"]');

        $linkedIn = $crawler->filter('.contact a[href="https://www.linkedin.com/in/kraus-marcel"]');
        self::assertSame('_blank', $linkedIn->attr('target'));
        self::assertStringContainsString('öffnet in neuem Tab', $linkedIn->text());

        self::assertStringNotContainsString('mailto:', (string) $client->getResponse()->getContent());
    }

    public function testValidSubmissionRedirects(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ]);

        $client->request('POST', '/kontakt', $payload);

        // The redirect alone proves nothing: the silent spam path answers with
        // exactly the same redirect and the same flash. Only the mail tells
        // the two apart.
        self::assertResponseRedirects('/kontakt');
        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(), 'To', $_SERVER['CONTACT_TO']);
        self::assertEmailAddressContains(self::getMailerMessage(), 'Reply-To', 'max@example.com');
    }

    public function testFilledHoneypotIsDroppedWithoutSendingAnything(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
            'website' => 'https://example.com',
        ]);

        $client->request('POST', '/kontakt', $payload);

        // A bot must learn nothing, so the answer is a fake success.
        self::assertResponseRedirects('/kontakt');
        self::assertEmailCount(0);
    }

    public function testSubmissionUnderThreeSecondsIsDroppedWithoutSendingAnything(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ], age: 0);

        $client->request('POST', '/kontakt', $payload);

        self::assertResponseRedirects();
        self::assertEmailCount(0);
    }

    public function testTamperedTimestampSignatureIsDroppedWithoutSendingAnything(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ]);
        $payload['ts_sig'] = 'gefälscht';

        $client->request('POST', '/kontakt', $payload);

        // The riskiest of the three silent drops: it also catches real people
        // after a rotation of APP_SECRET.
        self::assertResponseRedirects();
        self::assertEmailCount(0);
    }

    public function testSuccessShowsTheConfirmationInsteadOfTheForm(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ]);

        $client->request('POST', '/kontakt', $payload);
        $client->followRedirect();

        self::assertSame('/kontakt', $client->getRequest()->getPathInfo());
        self::assertSelectorTextContains('.contact', 'Vielen Dank');
        self::assertSelectorNotExists('form[action="/kontakt"]');
    }

    public function testTamperedCsrfTokenIsRejected(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ]);
        $payload['_token'] = 'gefälscht';

        $client->request('POST', '/kontakt', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
        self::assertSelectorTextContains('body', 'Sitzung ist abgelaufen');
    }

    public function testInvalidSubmissionReRendersTheContactPageWithErrorsAndInput(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => '',
            'email' => 'not-an-email',
            'company' => 'Beispiel GmbH',
            'message' => 'x',
        ]);

        $crawler = $client->request('POST', '/kontakt', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertSame('/kontakt', $crawler->filter('nav[aria-label="Bereiche"] a[aria-current="page"]')->attr('href'));
        self::assertSelectorTextContains('.contact', 'Bitte geben Sie Ihren Namen an.');
        self::assertSame('not-an-email', $crawler->filter('#f_email')->attr('value'));
        self::assertSame('Beispiel GmbH', $crawler->filter('#f_company')->attr('value'));
        self::assertSame('x', $crawler->filter('#f_message')->text());
        self::assertSame('name', $crawler->filter('form')->attr('data-error-focus'));
    }

    public function testExpiredTimestampReRendersInsteadOfFakingSuccess(): void
    {
        $client = static::createClient();
        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ], age: 8000);

        $client->request('POST', '/kontakt', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'zu lange geöffnet');
    }

    public function testTransportFailureIsAnsweredAsAFormErrorRatherThanAnErrorPage(): void
    {
        $client = static::createClient();

        // Both lines have to come before the first request. The kernel is
        // rebooted between requests by default, which would drop the
        // replacement; and once the container has handed the mailer out, it
        // refuses to replace it.
        $client->disableReboot();
        static::getContainer()->set(MailerInterface::class, new class implements MailerInterface {
            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                throw new TransportException('Der Test erzwingt einen Transportfehler.');
            }
        });

        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ]);

        $client->request('POST', '/kontakt', $payload);

        // Apache replaces the Symfony error page with its own, so an uncaught
        // transport exception costs the message and shows a bare 500 on the
        // one form this site exists for.
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'konnte gerade nicht zugestellt werden');
    }

    public function testAThrottledSubmissionIsRefusedAndSaysSo(): void
    {
        $client = static::createClient();

        // The limiter is replaced rather than exhausted: its counter lives in
        // the cache and outlives a single run, so a test that actually sends
        // six messages would poison every case that follows. A factory with a
        // limit of zero refuses on the first consume and touches nothing else.
        $client->disableReboot();
        static::getContainer()->set('limiter.contact_form', new class implements RateLimiterFactoryInterface {
            public function create(?string $key = null): LimiterInterface
            {
                return new class implements LimiterInterface {
                    public function consume(int $tokens = 1): RateLimit
                    {
                        return new RateLimit(0, new \DateTimeImmutable('+1 hour'), false, 5);
                    }

                    public function reserve(int $tokens = 1, ?float $maxTime = null): \Symfony\Component\RateLimiter\Reservation
                    {
                        throw new \LogicException('Nicht Teil dieses Tests.');
                    }

                    public function reset(): void
                    {
                        // Intentionally left blank.
                    }
                };
            }
        });

        $payload = $this->payload($client, [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'message' => 'Ich habe eine offene Stelle und würde gern mit Ihnen sprechen.',
        ]);

        $client->request('POST', '/kontakt', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'zu viele Nachrichten');
        self::assertEmailCount(0);
    }

    /**
     * Builds a POST payload with a valid CSRF token (from a rendered form) and
     * a signed timestamp aged `age` seconds—within the valid window by
     * default, or expired when a large age is passed.
     *
     * @param array<string, string> $fields
     *
     * @return array<string, string>
     */
    private function payload(KernelBrowser $client, array $fields, int $age = 5): array
    {
        $crawler = $client->request('GET', '/kontakt');
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $secret = static::getContainer()->getParameter('kernel.secret');

        $timestamp = (string) (time() - $age);

        return array_merge($fields, [
            '_token' => $token,
            'ts' => $timestamp,
            'ts_sig' => hash_hmac('sha256', $timestamp, $secret),
        ]);
    }
}
