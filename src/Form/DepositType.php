<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepositType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout du montant uniquement
        $builder
            ->add('amount', IntegerType::class, [
                'label' => 'Montant à déposer',
                'attr' => ['min' => 1],  
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null, // Option disponible mais inutile ici
        ]);
    }
}
