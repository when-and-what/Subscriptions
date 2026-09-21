<?php

namespace App\Enums;

enum BillingCycle: int
{
    case Monthly = 1;
    case Quarterly = 3;
    case SemiAnnually = 6;
    case Yearly = 12;

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::SemiAnnually => 'Semi-annually',
            self::Yearly => 'Yearly',
        };
    }
}
