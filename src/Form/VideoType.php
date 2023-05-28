<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('provider', ChoiceType::class, [
                'label' => 'Source',
                'choices' => [
                    'youtube' => 'youtube',
                    'dailymotion' => 'dailymotion',
                    'vimeo' => 'vimeo',
                ],
                'data' => 'youtube',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('video_id', null, [
                'label' => 'Url ou identifiant',
                'row_attr' => ['class' => 'form-floating mb-3'],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une url ou un identifiant',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
