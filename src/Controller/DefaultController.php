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

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_services', methods: ['GET'])]
    function services(): Response
    {
        return $this->render('default/services.html.twig');
    }

    #[Route('/stationen', name: 'app_milestones', methods: ['GET'])]
    function milestones(): Response
    {
        return $this->render('default/milestones.html.twig');
    }

    #[Route('/kontakt', name: 'app_contact', methods: ['GET', 'POST'])]
    function contact(Request $request, MailerInterface $mailer): Response
    {
        $contactForm = $this->createForm(ContactRequestType::class, new ContactRequest());

        if ($request->isMethod('post')) {
            $contactForm->handleRequest($request);

            if ($contactForm->isSubmitted() && $contactForm->isValid()) {
                $contactRequest = $contactForm->getData();
                $message = (new TemplatedEmail())
                    ->from($_SERVER['CONTACT_FORM_SENDER_ADDRESS'])
                    ->to($_SERVER['CONTACT_FORM_RECIPIENT_ADDRESS'])
                    ->replyTo($contactRequest->getEmail())
                    ->subject('Neue Nachricht erhalten')
                    ->textTemplate('message/contact-request.txt.twig')
                    ->context([
                        'name' => $contactRequest->getName(),
                        'emailAddress' => $contactRequest->getEmail(),
                        'message' => $contactRequest->getMessage(),
                    ]);

                $mailer->send($message);

                return $this->redirectToRoute('app_contact_confirmation');
            }
        }

        return $this->render('default/contact.html.twig', [
            'contactForm' => $contactForm,
        ]);
    }

    #[Route('/kontakt/vielen-dank', name: 'app_contact_confirmation', methods: ['GET'])]
    function contactConfirmation()
    {
        return $this->render('default/contact-confirmation.html.twig');
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