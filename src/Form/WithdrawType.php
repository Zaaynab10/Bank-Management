<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Repository\BankAccountRepository;


class WithdrawType extends AbstractType
{
    private BankAccountRepository $bankAccountRepository;

    public function __construct(BankAccountRepository $bankAccountRepository)
    {
        $this->bankAccountRepository = $bankAccountRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $bankAccounts = $this->bankAccountRepository->findBy(['owner' => $user]);

        if (empty($bankAccounts)) {
            throw new \Exception("No bank accounts found for this user.");
        }

        $builder
    ->add('bankAccount', ChoiceType::class, [
        'choices' => array_flip(array_map(function ($bankAccount) {
            return $bankAccount->getId() . ' - ' . $bankAccount->getType()->value.'-'.$bankAccount->getAccountNumber();
        }, $bankAccounts)),
        
        'mapped' => false,
        'label' => 'Select Account',
        'expanded' => false,
        'multiple' => false,
    ])

            ->add('amount', IntegerType::class, [
                'label' => 'Deposit Amount',
                'attr' => ['min' => 1],  
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null, 
    ]);
}

}
