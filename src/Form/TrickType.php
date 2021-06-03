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

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();

        $imgRequired = true;
        $arrayPhotos = [];

        if ($entity->getId()) { // if there is an ID =, it means we are in an edit
            $imgRequired = false;
        }
        if (count($entity->getPhotos())>0) {
            foreach ($entity->getPhotos() as $key => $value) {
                $arrayPhotos[$key] = $value;
            }
        }

        $builder
            ->add('title')
            ->add('trickGroup', ChoiceType::class, [
                'choices'  => Trick::GROUPS
            ])
            ->add('content', TextareaType::class)
            ->add('mainImg', FileType::class, [
                'required' => $imgRequired,
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
            // the mapped false should not have the same name as real entity attributes
            /* ->add('photosFiles', CollectionType::class, [
                'entry_type' => FileType::class,
                'mapped' => false,
                'label' => false,
                'data' => $arrayPhotos,
                'entry_options' => [
                    'attr' => ['class' => 'tricks_photo_class'],
                    'required' => false,
                ],
                'allow_add'=> true,
                'allow_delete'=> true,
                'prototype' => true,
            ]) */
            ->add('videos', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => false,
                'entry_options' => [
                    'attr' => ['class' => 'tricks_video_class'],
                ],
                'allow_add'=> true,
                'allow_delete'=> true
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true
            ])
            /* 
            ->add('videos')
            ->add('author') */;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'translation_domain' => 'forms'
        ]);
    }
}
