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

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'row_attr' => [
                    'class' => 'form-group mb-3',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du groupe',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom',
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-group mb-3',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description du groupe (facultatif)',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Group'
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
