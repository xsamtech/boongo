<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PricingDescription extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'pricing_descriptions';

    public array $translatable = ['description_title', 'description_content'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }
}
