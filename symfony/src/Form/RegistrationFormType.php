<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Email
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'autocomplete' => 'email',
                    'placeholder' => 'email@example.com',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre adresse e-mail.',
                    ]),
                    new Email([
                        'message' => 'Cette adresse e-mail n’est pas valide.',
                    ]),
                    new Length([
                        'max' => 180,
                        'maxMessage' => 'L’adresse e-mail ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])

            // Conditions générales (case à cocher)
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J’accepte les conditions d’utilisation',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions pour continuer.',
                    ]),
                ],
            ])

            // Mot de passe avec confirmation
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // encodé dans le contrôleur
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Votre mot de passe sécurisé',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Confirmez votre mot de passe',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un mot de passe.',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    // Au moins une lettre et un chiffre (tu peux renforcer si besoin)
                    new Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
                        'message' => 'Votre mot de passe doit contenir au moins une lettre et un chiffre.',
                    ]),
                    // Vérifie que le mot de passe n'est pas compromis (Have I Been Pwned)
                    new NotCompromisedPassword([
                        'message' => 'Ce mot de passe a été exposé lors d\'une fuite de données. Veuillez en choisir un autre.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
