<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'required' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse de livraison',
                'required' => true,
                'attr' => ['id' => 'address-input'], // Pour Google Maps autocomplete
            ])
            // Champs cachés pour Google Maps
            ->add('latitude', HiddenType::class, [
                'attr' => ['id' => 'latitude'],
            ])
            ->add('longitude', HiddenType::class, [
                'attr' => ['id' => 'longitude'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Si tu veux lier à l'entité Order, tu peux mettre : 'data_class' => Order::class
        ]);
    }
}