<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Pricing extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'pricings';

    public array $translatable = ['pricing_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'coins_amount' => 'integer',
        'premium_duration_months' => 'integer',
        'pricing_cost' => 'decimal:2',
    ];

    public function descriptions(): HasMany
    {
        return $this->hasMany(PricingDescription::class);
    }
}
