<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The page `bin/render-sharing-image` photographs into the sharing image,
 * registered in dev alone—see `docs/DESIGN.md` › SEO / meta.
 */
final class SharingImageController extends AbstractController
{
    #[Route('/sharing-image', name: 'app_sharing_image', methods: ['GET'], env: 'dev')]
    public function __invoke(): Response
    {
        return $this->render('sharing-image.html.twig');
    }
}
