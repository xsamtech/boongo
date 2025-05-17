<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Partner extends Model
{
    use HasFactory;

    protected $table = 'partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several categories for several partners
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_partner')->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot(['promo_code', 'number_of_days', 'number_of_registrations', 'status_id']);
    }

    /**
     * Calculate the remaining days since the partner's registration.
     *
     * @param \Carbon\Carbon $date
     * @return int
     */
    public function remainingDays($date)
    {
        // Retrieve the last record from the 'category_partner' table for this partner
        $pivotData = $this->categories()->latest('pivot_updated_at')->first()->pivot;

        // Get the number of days (number_of_days) and the date updated (updated_at)
        $numberOfDays = $pivotData->number_of_days;
        $updatedAt = Carbon::parse($pivotData->updated_at);

        // Calculate the number of days since the update date
        $daysSinceUpdate = $updatedAt->diffInDays($date);

        // Calculate the remaining days
        $remainingDays = $numberOfDays - $daysSinceUpdate;

        return $remainingDays > 0 ? $remainingDays : 0; // Ensure the number of days remaining is positive
    }
}
