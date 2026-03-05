<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'Adresse de livraison',
                'required' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
                'required' => true,
            ])
                ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => true,
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class car ce n’est pas lié à une entité
        ]);
    }
}