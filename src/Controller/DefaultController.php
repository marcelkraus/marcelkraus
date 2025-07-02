<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Form\Type\ContactRequestType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    function services(): Response
    {
        $serializer = new Serializer(
            [new ObjectNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );

        $content = $serializer->deserialize(
            file_get_contents("../config/content/homepage.json"),
            'App\Entity\Homepage',
            'json'
        );

        $contactForm = $this->createForm(ContactRequestType::class, new ContactRequest(), [
            'antispam_profile' => 'default',
        ]);

        return $this->render('default/homepage.html.twig', [
            'content' => $content,
            'contactForm' => $contactForm,
        ]);
    }

    #[Route('/', methods: ['POST'])]
    function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactRequestType::class, new ContactRequest(), [
            'antispam_profile' => 'default',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactRequest = $form->getData();
            $message = (new TemplatedEmail())
                ->from($_SERVER['CONTACT_FORM_SENDER_ADDRESS'])
                ->to($_SERVER['CONTACT_FORM_RECIPIENT_ADDRESS'])
                ->replyTo($contactRequest->getEmail())
                ->subject('Neue Kontaktanfrage erhalten')
                ->textTemplate('message/contact-request.txt.twig')
                ->context([
                    'name' => $contactRequest->getName(),
                    'emailAddress' => $contactRequest->getEmail(),
                    'message' => $contactRequest->getMessage(),
                ]);

            $mailer->send($message);

            $this->addFlash('success', '<default>');

            return $this->redirectToRoute('app_homepage');
        }
    }

    #[Route('/impressum', name: 'app_imprint', methods: ['GET'])]
    function imprint(): Response
    {
        return $this->render('default/imprint.html.twig');
    }

    #[Route('/datenschutz', name: 'app_data_privacy', methods: ['GET'])]
    function dataPrivacy(): Response
    {
        return $this->render('default/data-privacy.html.twig');
    }
}