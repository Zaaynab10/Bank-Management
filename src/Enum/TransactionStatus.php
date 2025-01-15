<?php
namespace App\Enum;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
}