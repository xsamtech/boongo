<?php

namespace App\Services;

use App\Exceptions\DirectPurchaseException;
use App\Models\CurrenciesRate;
use App\Models\Currency;

class UsdPriceConverter
{
    public function convert(mixed $price, ?Currency $currency): string
    {
        if (! is_numeric($price) || ! is_finite((float) $price) || $price <= 0 || $price > 9999999.99) {
            throw new DirectPurchaseException('Le prix doit être un montant strictement positif.');
        }
        if ($currency === null || ! $currency->currency_acronym) {
            throw new DirectPurchaseException(__('notifications.find_currency_404'), 404);
        }

        // SQL prices have two decimal places; use integer cents to avoid float rounding.
        $cents = (int) str_replace('.', '', number_format((float) $price, 2, '.', ''));
        if (strtoupper(trim($currency->currency_acronym)) !== 'USD') {
            $usd = Currency::where('currency_acronym', 'USD')->first();
            if ($usd === null) {
                throw new DirectPurchaseException(__('notifications.find_currency_404'), 404);
            }

            $rate = $this->rate($currency->id, $usd->id);
            $inverse = false;
            if ($rate === null) {
                $rate = $this->rate($usd->id, $currency->id);
                $inverse = true;
            }
            if ($rate === null) {
                throw new DirectPurchaseException(__('notifications.find_currencies_rate_404'), 404);
            }
            if (! is_numeric($rate->rate) || ! is_finite((float) $rate->rate) || $rate->rate <= 0 || $rate->rate > 9999.99999) {
                throw new DirectPurchaseException('Le taux de conversion vers USD doit être strictement positif et valide.');
            }

            // SQL rates have five decimal places. Both products fit in a 64-bit integer.
            $scaledRate = (int) str_replace('.', '', number_format((float) $rate->rate, 5, '.', ''));
            if ($scaledRate === 0) {
                throw new DirectPurchaseException('Le taux de conversion vers USD est trop faible.');
            }
            $numerator = $inverse ? $cents * 100000 : $cents * $scaledRate;
            $denominator = $inverse ? $scaledRate : 100000;
            $cents = intdiv($numerator, $denominator) + (($numerator % $denominator) * 2 >= $denominator ? 1 : 0);
        }

        if ($cents <= 0 || $cents > 999999999) {
            throw new DirectPurchaseException('Le montant converti en USD est nul ou dépasse la capacité de payments.amount.');
        }

        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function rate(int $from, int $to): ?CurrenciesRate
    {
        return CurrenciesRate::where('from_currency_id', $from)
            ->where('to_currency_id', $to)
            ->orderByDesc('created_at')->orderByDesc('id')->first();
    }
}
