<?php

namespace App\Services;

class ClaimCalculator
{
    /**
     * CLAIM = DISC NOMINAL kalau diisi, kalau tidak = PROMO DISC% * REG.
     */
    public static function claim(?float $discNominal, ?float $promoDiscPercent, float $reg): float
    {
        if ($discNominal !== null && $discNominal > 0) {
            return round($discNominal, 2);
        }

        return round((($promoDiscPercent ?? 0) / 100) * $reg, 2);
    }

    /**
     * Value {STORE} = CLAIM * Sales {STORE}.
     */
    public static function value(float $claim, float $salesQty): float
    {
        return round($claim * $salesQty, 2);
    }
}
