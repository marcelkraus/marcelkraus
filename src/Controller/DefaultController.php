<?php

namespace App\Controller;

use App\Entity\Hobby;
use App\Entity\Milestone;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_de_homepage', defaults: ['_locale' => 'de'], methods: ['GET'])]
    #[Route('/en', name: 'app_en_homepage', defaults: ['_locale' => 'en'], methods: ['GET'])]
    function homepage(Request $request, string $_locale): Response
    {
        $request->setLocale($_locale);

        $serializer = new Serializer(
            [new ObjectNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );

        $projectDir = $this->getParameter('kernel.project_dir');

        $allMilestones = $serializer->deserialize(
            file_get_contents($projectDir . '/config/content/milestones.json'),
            Milestone::class . '[]',
            'json'
        );

        $allProjects = $serializer->deserialize(
            file_get_contents($projectDir . '/config/content/projects.json'),
            Project::class . '[]',
            'json'
        );

        $allHobbies = $serializer->deserialize(
            file_get_contents($projectDir . '/config/content/hobbies.json'),
            Hobby::class . '[]',
            'json'
        );

        $milestones = array_values(array_filter(
            $allMilestones,
            fn(Milestone $milestone) => $milestone->getLanguage() === $_locale
        ));

        $projects = array_values(array_filter(
            $allProjects,
            fn(Project $project) => $project->getLanguage() === $_locale
        ));

        $hobbies = array_values(array_filter(
            $allHobbies,
            fn(Hobby $hobby) => $hobby->getLanguage() === $_locale
        ));

        return $this->render('default/homepage.html.twig', [
            'milestones' => $milestones,
            'projects' => $projects,
            'hobbies' => $hobbies,
            'switch_route' => $_locale === 'en' ? 'app_de_homepage' : 'app_en_homepage',
        ]);
    }

    #[Route('/impressum', name: 'app_de_imprint', defaults: ['_locale' => 'de'], methods: ['GET'])]
    #[Route('/en/imprint', name: 'app_en_imprint', defaults: ['_locale' => 'en'], methods: ['GET'])]
    function imprint(Request $request, string $_locale): Response
    {
        $request->setLocale($_locale);

        $template = $_locale === 'en'
            ? 'default/imprint.en.html.twig'
            : 'default/imprint.de.html.twig';

        return $this->render($template, [
            'switch_route' => $_locale === 'en' ? 'app_de_imprint' : 'app_en_imprint',
        ]);
    }

    #[Route('/datenschutz', name: 'app_de_data_privacy', defaults: ['_locale' => 'de'], methods: ['GET'])]
    #[Route('/en/privacy-policy', name: 'app_en_data_privacy', defaults: ['_locale' => 'en'], methods: ['GET'])]
    function dataPrivacy(Request $request, string $_locale): Response
    {
        $request->setLocale($_locale);

        $template = $_locale === 'en'
            ? 'default/data-privacy.en.html.twig'
            : 'default/data-privacy.de.html.twig';

        return $this->render($template, [
            'switch_route' => $_locale === 'en' ? 'app_de_data_privacy' : 'app_en_data_privacy',
        ]);
    }

    #[Route('/kontakt-per-whats-app', name: 'app_contact_whats_app', methods: ['GET'])]
    function whatsAppRedirect(): Response
    {
        return $this->redirect('https://wa.me/message/TKVKBR7RPHA7B1');
    }
}
