<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $imgRequired = true;

        if ($entity->getId()) {
            $imgRequired = false;
        }

        $builder
            ->add('mail', EmailType::class)
            ->add('username', TextType::class)
            // tester de changer le username (unicité)
            ->add('photoFile', FileType::class, [
                'required' => $imgRequired,
                'mapped' => false,
                'label' => false,
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
            ;
            // this form will also test (by validators) the password even if it is not in the adds
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms'
        ]);
    }
}
