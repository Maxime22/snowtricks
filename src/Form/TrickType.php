<?php

namespace App\Form;

use App\Entity\Trick;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\Regex;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('trickGroup', ChoiceType::class, [
                'choices'  => Trick::GROUPS
            ])
            ->add('content', TextareaType::class)
            ->add('mainImg', FileType::class, [
                'required' => false,
                // unmapped options means that this field is not associated to any entity property
                'mapped' => false,
                'label' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPEG document',
                    ])
                ],
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => false,
                'entry_options' => [
                    'attr' => ['class' => 'tricks_video_class'],
                    'label' => false,
                    'constraints' => [new Regex(['pattern'=> '/https:\/\/www.youtube.com\/embed\//','message'=>"Votre lien doit ??tre de la forme : https://www.youtube.com/embed/..."])]
                ],
                'allow_add'=> true,
                'allow_delete'=> true
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'translation_domain' => 'forms'
        ]);
    }
}
