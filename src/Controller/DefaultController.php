<?php

declare(strict_types=1);

namespace App\Controller;

use App\Content\ContentRepository;
use App\Dto\ContactRequest;
use App\Pdf\CurriculumVitaeRenderer;
use App\Service\AvailabilityCalculator;
use App\Service\GermanDateFormatter;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DefaultController extends AbstractController
{
    public function __construct(
        private readonly AvailabilityCalculator $availabilityCalculator,
        private readonly ContentRepository $content,
        private readonly CurriculumVitaeRenderer $curriculumVitaeRenderer,
        private readonly GermanDateFormatter $dateFormatter,
        private readonly MailerInterface $mailer,
        private readonly ValidatorInterface $validator,
        #[Autowire('%kernel.secret%')]
        private readonly string $appSecret,
        #[Autowire('%env(CONTACT_TO)%')]
        private readonly string $contactTo,
        #[Autowire('%env(CONTACT_FROM)%')]
        private readonly string $contactFrom,
        #[Autowire(service: 'limiter.contact_form')]
        private readonly RateLimiterFactoryInterface $contactFormLimiter,
    ) {
    }

    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function homepage(): Response
    {
        return $this->renderHomepage();
    }

    #[Route('/kontakt', name: 'app_contact', methods: ['POST'])]
    public function contact(Request $request): Response
    {
        $timestampState = $this->timestampState($request);

        // Silent drop only for clear bot signals — a filled honeypot, a
        // missing/tampered signature, or an inhumanly fast submission. These
        // get a fake "success" so bots learn nothing; nothing is sent.
        $honeypot = trim((string) $request->request->get('website', ''));
        if ($honeypot !== '' || $timestampState === 'invalid' || $timestampState === 'too_fast') {
            $this->addFlash('contact_success', true);

            return $this->redirect($this->generateUrl('app_homepage', ['_fragment' => 'kontakt']));
        }

        $data = new ContactRequest();
        $data->name = trim((string) $request->request->get('name', ''));
        $data->email = trim((string) $request->request->get('email', ''));
        $data->company = trim((string) $request->request->get('company', ''));
        $data->phone = trim((string) $request->request->get('phone', ''));
        $data->message = trim((string) $request->request->get('message', ''));

        // Keep the first violation per field: the constraints are written in
        // order of relevance, so an empty message must report NotBlank rather
        // than the minimum-length rule that fires alongside it.
        $errors = [];
        foreach ($this->validator->validate($data) as $violation) {
            $errors[$violation->getPropertyPath()] ??= $violation->getMessage();
        }

        if ($this->isCsrfTokenValid('contact', (string) $request->request->get('_token')) === false) {
            $errors['form'] = 'Ihre Sitzung ist abgelaufen. Bitte senden Sie das Formular erneut ab.';
        }

        // A valid but stale signature is a real user whose form sat open too
        // long — never silently drop it, ask them to resend instead.
        if ($timestampState === 'expired') {
            $errors['form'] = 'Das Formular war zu lange geöffnet. Bitte senden Sie das Formular erneut ab.';
        }

        if ($errors !== []) {
            return $this->renderContactErrors($request, $errors);
        }

        // Throttle per IP so a scraped token cannot be replayed into a flood.
        if ($this->contactFormLimiter->create($request->getClientIp() ?? 'anonymous')->consume(1)->isAccepted() === false) {
            return $this->renderContactErrors($request, [
                'form' => 'Es sind zu viele Nachrichten eingegangen. Bitte versuchen Sie es später noch einmal.',
            ]);
        }

        // A transport failure must not cost the message and must not hand a
        // visitor a bare error page: the sibling project has the two ways an
        // Uberspace sendmail DSN can be wrong on record, and this form is the
        // only reason the site exists.
        try {
            $this->mailer->send(
                (new TemplatedEmail())
                    ->from(new Address($this->contactFrom, 'Marcel Kraus'))
                    ->to($this->contactTo)
                    ->replyTo(new Address($data->email, $data->name))
                    ->subject(sprintf('Nachricht von %s', $data->name))
                    ->textTemplate('default/contact.txt.twig')
                    ->context([
                        'company' => $data->company,
                        'emailAddress' => $data->email,
                        'message' => $data->message,
                        'name' => $data->name,
                        'phone' => $data->phone,
                    ])
            );
        } catch (TransportExceptionInterface) {
            return $this->renderContactErrors($request, [
                'form' => 'Die Nachricht konnte gerade nicht zugestellt werden. Bitte versuchen Sie es später noch einmal oder schreiben Sie mir per E-Mail.',
            ]);
        }

        $this->addFlash('contact_success', true);

        return $this->redirect($this->generateUrl('app_homepage', ['_fragment' => 'kontakt']));
    }

    /**
     * The address never appears in the markup — the link points at a route
     * and the route redirects. That keeps the mailto out of a shape that is
     * trivial to scrape, which is the same reason the legal pages spell the
     * address out in words.
     */
    #[Route('/kontakt-per-email', name: 'app_contact_email', methods: ['GET'])]
    public function contactByEmail(): Response
    {
        return $this->redirect('mailto:' . $this->getParameter('app.contact_email_address'));
    }

    #[Route('/kontakt-per-whats-app', name: 'app_contact_whats_app', methods: ['GET'])]
    public function contactByWhatsApp(): Response
    {
        return $this->redirect($this->getParameter('app.whats_app_url'));
    }

    /**
     * The printed curriculum vitae, generated on request from the same content
     * files the page renders. Not a second document: a hand-kept PDF drifts
     * against the site, and then there are two truths about one career.
     *
     * `noindex` because the file duplicates the homepage and carries the
     * postal address — the indexed truth stays the page. A `Disallow` in
     * robots.txt would be worse than useless here: it stops a crawler from
     * ever reading the header that tells it not to index.
     */
    #[Route('/lebenslauf', name: 'app_curriculum_vitae_pdf', methods: ['GET'])]
    public function curriculumVitaePdf(): Response
    {
        $today = new DateTimeImmutable();

        $milestones = $this->content->load('milestones');

        $bySection = static fn (callable $matches): array => array_values(
            array_filter($milestones, static fn (array $milestone): bool => $matches($milestone['marker'] ?? null)),
        );

        $context = [
            'claim' => 'Nicht nur mit Hut. Mit Köpfchen.',
            'profile' => $this->getParameter('app.curriculum_vitae_profile'),
            // No LinkedIn row and no second QR code: the homepage links the
            // profile anyway, and two codes side by side are indistinguishable
            // without reading their labels.
            'contact' => [
                ['label' => 'Datum', 'value' => $this->dateFormatter->dayMonthAndYear($today)],
                ['label' => 'Anschrift', 'value' => 'Bonner Straße 88<br>50374 Erftstadt'],
                ['label' => 'Telefon', 'value' => $this->getParameter('app.phone_number')],
                ['label' => 'Verfügbar', 'value' => $this->availabilityCalculator->availabilityLabel($today)],
                ['label' => 'E-Mail', 'value' => $this->getParameter('app.contact_email_address')],
                ['label' => 'Portfolio & Profile', 'value' => 'www.marcelkraus.de'],
            ],
            'sections' => [
                [
                    'title' => 'Berufserfahrung',
                    'milestones' => $bySection(static fn (?string $marker): bool => $marker === null),
                ],
                [
                    'title' => 'Selbstständigkeit',
                    'milestones' => $bySection(static fn (?string $marker): bool => $marker !== null && $marker !== 'secondary'),
                ],
                [
                    'title' => 'Ausbildung',
                    'milestones' => $bySection(static fn (?string $marker): bool => $marker === 'secondary'),
                ],
            ],
            'markerPath' => $this->getParameter('kernel.project_dir') . '/public/images/marker-accent.svg',
            'qrCodePath' => $this->getParameter('kernel.project_dir') . '/public/images/qr-code.svg',
            'skills' => $this->content->load('skills'),
            'languages' => 'Englisch (B2)',
        ];

        $response = new Response($this->curriculumVitaeRenderer->render($context));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set(
            'Content-Disposition',
            // Inline, not an attachment: the document is meant to be read in
            // the browser first. The file name still travels with it, so
            // saving it produces the right name anyway.
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                'Lebenslauf-Marcel-Kraus.pdf',
            ),
        );
        $response->headers->set('X-Robots-Tag', 'noindex');
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }

    #[Route('/impressum', name: 'app_imprint', methods: ['GET'])]
    public function imprint(): Response
    {
        return $this->render('default/imprint.html.twig');
    }

    #[Route('/datenschutz', name: 'app_data_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('default/data-privacy.html.twig');
    }

    /**
     * The site that stood here until 2026 carried an English translation under
     * /en, and that path is in the old sitemap. It is gone with the rebuild —
     * one language now — so the old address is sent to the homepage rather
     * than answered with a 404.
     */
    #[Route('/en', name: 'app_legacy_english', methods: ['GET'])]
    #[Route('/en/{path}', name: 'app_legacy_english_path', requirements: ['path' => '.*'], methods: ['GET'])]
    public function legacyEnglish(): Response
    {
        return $this->redirect($this->generateUrl('app_homepage'), Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $sitemap = $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // The redirect routes carry no document, only a Location header — and
        // for the two contact routes that header holds the address. A
        // well-behaved crawler follows it and takes the address into its
        // corpus, and those corpora are where address lists come from.
        // Harvesters ignore robots.txt, but the corpora do not.
        $disallowed = [
            '/kontakt-per-email',
            '/kontakt-per-whats-app',
        ];

        $rules = "User-agent: *\n";
        foreach ($disallowed as $route) {
            $rules .= "Disallow: {$route}\n";
        }

        return new Response(
            $rules . "\nSitemap: {$sitemap}\n",
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        // One page carries the whole curriculum vitae. The legal pages are
        // noindex and stay out.
        $location = $this->generateUrl('app_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . "    <url>\n        <loc>{$location}</loc>\n        <changefreq>monthly</changefreq>\n        <priority>1.0</priority>\n    </url>\n"
            . "</urlset>\n";

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Renders the homepage, always seeding a fresh anti-spam timestamp and
     * the JSON-driven content.
     *
     * @param array<string, mixed> $formState
     */
    private function renderHomepage(array $formState = [], int $status = Response::HTTP_OK): Response
    {
        $timestamp = (string) time();
        $hobbies = $this->content->load('hobbies');
        $skills = $this->content->load('skills');

        $response = $this->render('default/homepage.html.twig', array_merge([
            'availability' => $this->availabilityCalculator->availabilityLabel(new DateTimeImmutable()),
            'brands' => $this->content->load('brands'),
            'hobbies' => $hobbies,
            'knowsAbout' => $this->knowsAbout($skills, $hobbies),
            'milestones' => $this->content->load('milestones'),
            'skills' => $skills,
            'contact_ts' => $timestamp,
            'contact_ts_sig' => $this->signTimestamp($timestamp),
            'contact_errors' => [],
            'contact_old' => [],
            'contact_focus' => null,
        ], $formState));
        $response->setStatusCode($status);

        return $response;
    }

    /**
     * Re-renders the homepage with contact-form errors, the submitted values
     * and the first errored field name so the client can focus it.
     *
     * @param array<string, string> $errors
     */
    private function renderContactErrors(Request $request, array $errors): Response
    {
        $focus = null;
        foreach (array_keys($errors) as $field) {
            if ($field !== 'form') {
                $focus = $field;
                break;
            }
        }

        $formState = [
            'contact_errors' => $errors,
            'contact_old' => $request->request->all(),
            'contact_focus' => $focus,
        ];

        // Reuse the visitor's still-valid timestamp on re-render so a quick
        // fix-and-resend is not misclassified as a bot ("too_fast"). Only seed
        // a fresh timestamp when none is reusable (missing, tampered, expired).
        $submittedTimestamp = (string) $request->request->get('ts', '');
        $submittedSignature = (string) $request->request->get('ts_sig', '');
        if ($submittedTimestamp !== ''
            && hash_equals($this->signTimestamp($submittedTimestamp), $submittedSignature)
            && time() - (int) $submittedTimestamp <= 7200
        ) {
            $formState['contact_ts'] = $submittedTimestamp;
            $formState['contact_ts_sig'] = $submittedSignature;
        }

        return $this->renderHomepage($formState, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Builds the JSON-LD `knowsAbout` list from the content files instead of
     * a hardcoded copy in the template: the skills carry the professional
     * terms, the hobbies the private ones. Sorted and deduplicated, so the
     * output does not shift when a content file is reordered.
     *
     * @param array<int, array<string, mixed>> $skills
     * @param array<int, array<string, mixed>> $hobbies
     *
     * @return list<string>
     */
    private function knowsAbout(array $skills, array $hobbies): array
    {
        $terms = [];

        foreach ($skills as $group) {
            foreach ($group['entries'] ?? [] as $entry) {
                if (isset($entry['name'])) {
                    $terms[] = (string) $entry['name'];
                }
            }
        }

        foreach ($hobbies as $hobby) {
            foreach ($hobby['knowsAbout'] ?? [] as $term) {
                $terms[] = (string) $term;
            }
        }

        $terms = array_values(array_unique($terms));
        sort($terms);

        return $terms;
    }

    private function signTimestamp(string $timestamp): string
    {
        return hash_hmac('sha256', $timestamp, $this->appSecret);
    }

    /**
     * Classifies the signed anti-spam timestamp: 'invalid' (missing or
     * tampered signature), 'too_fast' (submitted within 3 s), 'expired'
     * (older than two hours) or 'valid'.
     */
    private function timestampState(Request $request): string
    {
        $timestamp = (string) $request->request->get('ts', '');
        $signature = (string) $request->request->get('ts_sig', '');

        if ($timestamp === '' || hash_equals($this->signTimestamp($timestamp), $signature) === false) {
            return 'invalid';
        }

        $elapsed = time() - (int) $timestamp;

        if ($elapsed < 3) {
            return 'too_fast';
        }

        if ($elapsed > 7200) {
            return 'expired';
        }

        return 'valid';
    }
}
