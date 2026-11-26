<?php

declare(strict_types=1);

namespace App\Controller;

use App\Content\ContentRepository;
use App\Pdf\CurriculumVitaeRenderer;
use App\Service\GermanDateFormatter;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DefaultController extends AbstractController
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly CurriculumVitaeRenderer $curriculumVitaeRenderer,
        private readonly GermanDateFormatter $germanDateFormatter,
    ) {
    }

    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function homepage(): Response
    {
        return $this->render('content/homepage.html.twig', [
            'brands' => $this->content->load('brands'),
            'works' => $this->content->load('works'),
        ]);
    }

    #[Route('/lebenslauf', name: 'app_curriculum_vitae_pdf', methods: ['GET'])]
    public function curriculumVitaePdf(Request $request): Response
    {
        $today = new DateTimeImmutable();

        $withContact = $request->query->has('kontakt');

        $milestones = $this->content->load('milestones');

        $bySection = static fn (callable $matches): array => array_values(
            array_filter($milestones, static fn (array $milestone): bool => $matches($milestone['marker'] ?? null)),
        );

        $context = [
            'claim' => ['Nicht nur mit Hut.', 'Mit Köpfchen.'],
            'profile' => $this->getParameter('app.curriculum_vitae_profile'),
            'contact' => array_values(array_filter([
                ['label' => 'Datum', 'value' => $this->germanDateFormatter->dayMonthAndYear($today)],
                ['label' => 'Anschrift', 'value' => 'Bonner Str. 88<br>50374 Erftstadt'],
                $withContact ? ['label' => 'Telefon', 'value' => $this->getParameter('app.phone_number')] : null,
                ['label' => 'Verfügbar', 'value' => 'Ab Dezember'],
                $withContact ? ['label' => 'E-Mail', 'value' => $this->getParameter('app.contact_email_address')] : null,
                ['label' => 'Homepage', 'value' => 'www.marcelkraus.de'],
            ])),
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

        return new Response(
            "User-agent: *\nDisallow:\n\nSitemap: {$sitemap}\n",
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $location = $this->generateUrl('app_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . "    <url>\n        <loc>{$location}</loc>\n        <changefreq>monthly</changefreq>\n    </url>\n"
            . "</urlset>\n";

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
