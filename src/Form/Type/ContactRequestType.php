<?php

namespace App\Form\Type;

use App\Entity\ContactRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $form = $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-Mail-Adresse',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Nachricht',
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Nachricht senden'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactRequest::class,
        ]);
    }
}
