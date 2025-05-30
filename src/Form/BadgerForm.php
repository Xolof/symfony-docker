<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class BadgerForm extends AbstractType
{
    /**
     * Customize the form for editing a badger.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedExtensions = [
            'jpg',
            'jpeg',
            'webp',
            'png'
        ];
        $fileConstraint = new File(
            maxSize: '500k',
            extensions: $allowedExtensions,
            extensionsMessage: "Please upload an image file in one of the following formats: " . implode(", ", $allowedExtensions),
            maxSizeMessage: "The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}."
        );

        $builder->add('name', TextType::class)
            ->add('continent', TextType::class)
            ->add('description', TextareaType::class)
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => $options["image_is_required"],
                'constraints' => [$fileConstraint]
            ])
            ->add('imageFilename', HiddenType::class, [
                'empty_data' => "placeholder.jpg",
                'required' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'Save Badger'])
            ->getForm();
    }

    /**
     * Configure options for the form.
     *
     * The form can be used like this:
     * $form = $this->createForm(BadgerForm::class, $badger, [
     *     'image_is_required' => false,
     * ]);
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'image_is_required' => true
        ]);
    }
}
