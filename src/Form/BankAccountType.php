<?php

namespace App\Form;

use App\Enum\BankAccountType as EnumBankAccountType; 
use App\Entity\BankAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BankAccountType extends AbstractType  
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Savings' => EnumBankAccountType::SAVINGS,
                    'Current' => EnumBankAccountType::CURRENT,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Select Account Type',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAccount::class,
        ]);
    }

}
