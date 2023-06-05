<?php

namespace App\Form;

use App\Entity\Group;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\UniqueValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('Name'),
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group mb-3',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('Group name'),
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please enter a group name', [], 'validators')
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description'),
                'row_attr' => [
                    'class' => 'form-group mb-3',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('Group description (optional)'),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->translator->trans('Create Group'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
