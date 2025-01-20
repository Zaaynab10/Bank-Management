<?php
namespace App\Enum;

enum BankAccountStatus : string {
    case ACTIVE= 'active';
    case CLOSE = 'close';
}