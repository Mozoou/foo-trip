<?php

namespace App\Form;

use App\Entity\Destination;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DestinationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['placeholder' => 'e.g. Paris'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4, 'placeholder' => 'Describe the destination...'],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price (€)',
                'scale' => 2,
                'attr' => ['placeholder' => '100.00'],
            ])
            ->add('duration', TextType::class, [
                'label' => 'Duration',
                'attr' => ['placeholder' => 'e.g. 7 days'],
            ])
            ->add('image', TextType::class, [
                'label' => 'Image URL',
                'attr' => ['placeholder' => 'https://example.com/image.jpg'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Destination::class,
        ]);
    }
}
