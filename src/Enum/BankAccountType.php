<?php
namespace App\Enum;

enum BankAccountType : string {
    case SAVINGS = 'savings';
    case CURRENT = 'current';
}