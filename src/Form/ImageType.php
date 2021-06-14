<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* $builder
            ->add('file', FileType::class, ['label'=>false])
        ; */
        // https://stackoverflow.com/questions/21862168/calling-builder-getdata-from-within-a-nested-form-always-returns-null
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $image = $event->getData();
            $form = $event->getForm();

            $imgRequired = false;
    
            // check if the Image object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Image"
            if (!$image || null === $image->getId()) {
                $imgRequired = true;
            }
            $form->add('file', FileType::class, ['label'=>false, 'required' => $imgRequired]);
        });
    }

    

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}