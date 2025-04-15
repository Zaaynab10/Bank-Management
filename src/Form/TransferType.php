<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('source_account', EntityType::class, [
                'class' => 'App\Entity\BankAccount',
                'choices' => $options['bank_accounts'], // Utiliser l'option bank_accounts
                'choice_label' => function ($account) {
                    return $account->getAccountNumber() . ' (Solde: ' . $account->getBalance() . ' €)';
                },
                'label' => 'Compte source',
            ])
            ->add('destination_account_number', ChoiceType::class, [
                'choices' => $options['beneficiaries'], // Utiliser l'option beneficiaries
                'choice_label' => function ($beneficiary) {
                    return $beneficiary->getName() . ' - ' . $beneficiary->getBankAccountNumber();
                },
                'mapped' => false, // Pas directement lié à l'entité Transaction
                'label' => 'Compte destinataire',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Montant',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'bank_accounts' => [], // Option par défaut pour bank_accounts
            'beneficiaries' => [], // Option par défaut pour beneficiaries
            'user' => null, // Ajout de l'option user
        ]);
    }
}
