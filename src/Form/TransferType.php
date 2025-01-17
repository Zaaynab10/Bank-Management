<?php

namespace App\Form;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('amount', IntegerType::class, [
                'label' => 'Montant',
                'attr' => ['min' => 1],
            ])
            ->add('source_account', EntityType::class, [
                'class' => BankAccount::class,
                'choices' => $options['bank_accounts'],
                'choice_label' => function (BankAccount $account) {
                    return $account->getAccountNumber() . ' - ' . $account->getType()->value;
                },
                'label' => 'Compte source',
                'placeholder' => 'Sélectionnez un compte',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('destination_account_number', TextType::class, [
                'label' => 'Numéro de compte destinataire',
                'mapped' => false, 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null, 
            'bank_accounts' => [], 
   ]);
}
}
