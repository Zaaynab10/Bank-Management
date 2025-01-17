<?php
namespace App\Enum;

enum TransactionStatus: string
{
    case SUCCESSED = 'successed';
    case FAILED = 'failed';
}