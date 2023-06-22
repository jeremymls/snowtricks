<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrickType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description', CKEditorType::class, [
                'label' => $this->translator->trans('Content'),
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbarGroups' => $this->getToolbar(),
                    'removeButtons' => 'About',
                ],
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'label' => false,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => Group::class,
                'placeholder' => '------',
                'multiple' => false,
                'expanded' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('Please choose a group', [], 'validators'),
                    ]),
                ],
            ])
            ->add('images', FileType::class, [
                'label' => $this->translator->trans('Add images'),
                'help' => 'JPG, PNG or GIF',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
            ])
            ->add('miniature', HiddenType::class, [])
            ->add('save', SubmitType::class)
        ;
    }

    private function getToolbar()
    {
        return [
            [ 'name' => 'tools', 'groups' => [ 'tools' ] ],
            [ 'name' => 'document', 'groups' => [ 'mode', 'document', 'doctools' ] ],
            [ 'name' => 'clipboard', 'groups' => [ 'undo', 'clipboard' ] ],
            [ 'name' => 'editing', 'groups' => [ 'find', 'selection', 'spellchecker', 'editing' ] ],
            [ 'name' => 'forms', 'groups' => [ 'forms' ] ],
            '/',
            [ 'name' => 'basicstyles', 'groups' => [ 'basicstyles', 'cleanup' ] ],
            [ 'name' => 'paragraph', 'groups' => [ 'list', 'blocks', 'align', 'indent', 'bidi', 'paragraph' ] ],
            '/',
            [ 'name' => 'styles', 'groups' => [ 'styles' ] ],
            [ 'name' => 'colors', 'groups' => [ 'colors' ] ],
            [ 'name' => 'others', 'groups' => [ 'others' ] ],
            [ 'name' => 'about', 'groups' => [ 'about' ]],
            [ 'name' => 'links', 'groups' => [ 'links' ] ],
            [ 'name' => 'insert', 'groups' => [ 'insert' ] ],
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
