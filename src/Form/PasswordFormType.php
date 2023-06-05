<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordFormType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('actualPassword', PasswordType::class, [
                'label' => $this->translator->trans('Actual'),
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please enter your password', [], 'validators'),
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->translator->trans('The password must contain at least {{ min }} characters', ['{{ min }}' => 6], 'validators'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => $this->translator->trans('Passwords do not match', [], 'validators'),
                'required' => true,
                'first_options' => ['label' => $this->translator->trans('New')],
                'second_options' => ['label' => $this->translator->trans('Confirm')],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please enter a password', [], 'validators'),
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->translator->trans('The password must contain at least {{ min }} characters', ['{{ min }}' => 6], 'validators'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Change password'),
                'attr' => ['class' => 'btn btn-primary'],
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
