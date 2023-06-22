<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class VideoType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('provider', ChoiceType::class, [
                'label' => $this->translator->trans('Provider'),
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
                'label' => $this->translator->trans('Url or identifier'),
                'row_attr' => ['class' => 'form-floating mb-3'],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please enter an url or an identifier', [], 'validators'),
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
