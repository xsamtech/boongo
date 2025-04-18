<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Category extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Translatable properties.
     */
    protected $translatable = ['category_name'];

    /**
     * MANY-TO-ONE
     * Several works for several categories
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class);
    }

    /**
     * MANY-TO-MANY
     * Several partners for several categories
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'category_partner')->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot(['number_of_days']);
    }

    /**
     * ONE-TO-MANY
     * One group for several categories
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * MANY-TO-ONE
     * Several subscriptions for a category
     */
    public function subscriptions(): HasMany
    {
        return $this->HasMany(Subscription::class);
    }
}
