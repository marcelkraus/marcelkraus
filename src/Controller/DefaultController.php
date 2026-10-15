<?php

declare(strict_types=1);

namespace App\Controller;

use App\Content\ContentRepository;
use App\Dto\ContactRequest;
use App\Pdf\CurriculumVitaeRenderer;
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
        private readonly ContentRepository $content,
        private readonly CurriculumVitaeRenderer $curriculumVitaeRenderer,
        private readonly GermanDateFormatter $germanDateFormatter,
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
        $hobbies = $this->content->load('hobbies');
        $skills = $this->content->load('skills');

        return $this->render('content/homepage.html.twig', [
            'hobbies' => $hobbies,
            'knowsAbout' => $this->knowsAbout($skills, $hobbies),
            'milestones' => $this->content->load('milestones'),
            'skills' => $skills,
        ]);
    }

    #[Route('/portfolio', name: 'app_portfolio', methods: ['GET'])]
    public function portfolio(): Response
    {
        return $this->render('content/portfolio.html.twig', [
            'brands' => $this->content->load('brands'),
            'works' => $this->content->load('works'),
        ]);
    }

    #[Route('/kontakt', name: 'app_contact_page', methods: ['GET'])]
    public function contactPage(): Response
    {
        return $this->renderContactPage();
    }

    #[Route('/kontakt', name: 'app_contact', methods: ['POST'])]
    public function contact(Request $request): Response
    {
        $timestampState = $this->timestampState($request);

        // A clear bot signal gets a fake success, so the bot learns nothing.
        $honeypot = trim((string) $request->request->get('website', ''));
        if ($honeypot !== '' || $timestampState === 'invalid' || $timestampState === 'too_fast') {
            $this->addFlash('contact_success', true);

            return $this->redirect($this->generateUrl('app_contact_page'));
        }

        $data = new ContactRequest();
        $data->name = trim((string) $request->request->get('name', ''));
        $data->email = trim((string) $request->request->get('email', ''));
        $data->company = trim((string) $request->request->get('company', ''));
        $data->phone = trim((string) $request->request->get('phone', ''));
        $data->message = trim((string) $request->request->get('message', ''));

        // The constraints are written in order of relevance; the first one wins.
        $errors = [];
        foreach ($this->validator->validate($data) as $violation) {
            $errors[$violation->getPropertyPath()] ??= $violation->getMessage();
        }

        if ($this->isCsrfTokenValid('contact', (string) $request->request->get('_token')) === false) {
            $errors['form'] = 'Ihre Sitzung ist abgelaufen. Bitte senden Sie das Formular erneut ab.';
        }

        // A stale signature is a real visitor whose form sat open too long.
        if ($timestampState === 'expired') {
            $errors['form'] = 'Das Formular war zu lange geöffnet. Bitte senden Sie das Formular erneut ab.';
        }

        if ($errors !== []) {
            return $this->renderContactErrors($request, $errors);
        }

        if ($this->contactFormLimiter->create($request->getClientIp() ?? 'anonymous')->consume(1)->isAccepted() === false) {
            return $this->renderContactErrors($request, [
                'form' => 'Es sind zu viele Nachrichten eingegangen. Bitte versuchen Sie es später noch einmal.',
            ]);
        }

        try {
            $this->mailer->send(
                (new TemplatedEmail())
                    ->from(new Address($this->contactFrom, 'Marcel Kraus'))
                    ->to($this->contactTo)
                    ->replyTo(new Address($data->email, $data->name))
                    ->subject(sprintf('Nachricht von %s', $data->name))
                    ->textTemplate('content/contact.txt.twig')
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

        return $this->redirect($this->generateUrl('app_contact_page'));
    }

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

    #[Route('/lebenslauf', name: 'app_curriculum_vitae_pdf', methods: ['GET'])]
    public function curriculumVitaePdf(): Response
    {
        $today = new DateTimeImmutable();

        $milestones = $this->content->load('milestones');

        $bySection = static fn (callable $matches): array => array_values(
            array_filter($milestones, static fn (array $milestone): bool => $matches($milestone['marker'] ?? null)),
        );

        $context = [
            'claim' => ['Nicht nur mit Hut.', 'Mit Köpfchen.'],
            'profile' => $this->getParameter('app.curriculum_vitae_profile'),
            'contact' => [
                ['label' => 'Datum', 'value' => $this->germanDateFormatter->dayMonthAndYear($today)],
                ['label' => 'Anschrift', 'value' => 'Bonner Str. 88<br>50374 Erftstadt'],
                ['label' => 'Telefon', 'value' => $this->getParameter('app.phone_number')],
                ['label' => 'Verfügbar', 'value' => 'Ab Dezember'],
                ['label' => 'E-Mail', 'value' => $this->getParameter('app.contact_email_address')],
                ['label' => 'Homepage', 'value' => 'www.marcelkraus.de'],
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
            'markerPath' => $this->getParameter('kernel.project_dir') . '/public/images/marker-ink.svg',
            'qrCodePath' => $this->getParameter('kernel.project_dir') . '/public/images/qr-code.svg',
            'skills' => $this->content->load('skills'),
            'languages' => 'Englisch (B2)',
        ];

        $response = new Response($this->curriculumVitaeRenderer->render($context));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set(
            'Content-Disposition',
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
        return $this->render('content/imprint.html.twig');
    }

    #[Route('/datenschutz', name: 'app_data_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('content/data-privacy.html.twig');
    }

    // The former English pages still stand in old links.
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
        $entries = '';
        foreach ($this->getParameter('app.pages') as $page) {
            $location = $this->generateUrl($page['route'], [], UrlGeneratorInterface::ABSOLUTE_URL);
            $entries .= "    <url>\n        <loc>{$location}</loc>\n        <changefreq>monthly</changefreq>\n    </url>\n";
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . $entries
            . "</urlset>\n";

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * @param array<string, mixed> $formState
     */
    private function renderContactPage(array $formState = [], int $status = Response::HTTP_OK): Response
    {
        $timestamp = (string) time();

        $response = $this->render('content/contact.html.twig', array_merge([
            'contact_timestamp' => $timestamp,
            'contact_timestamp_signature' => $this->signTimestamp($timestamp),
            'contact_errors' => [],
            'contact_old' => [],
            'contact_focus' => null,
        ], $formState));
        $response->setStatusCode($status);

        return $response;
    }

    /**
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

        // A fresh timestamp would make a quick fix-and-resend look too fast.
        $submittedTimestamp = (string) $request->request->get('ts', '');
        $submittedSignature = (string) $request->request->get('ts_sig', '');
        if ($submittedTimestamp !== ''
            && hash_equals($this->signTimestamp($submittedTimestamp), $submittedSignature)
            && time() - (int) $submittedTimestamp <= 7200
        ) {
            $formState['contact_timestamp'] = $submittedTimestamp;
            $formState['contact_timestamp_signature'] = $submittedSignature;
        }

        return $this->renderContactPage($formState, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
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
