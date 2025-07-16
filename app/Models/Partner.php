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
        return $this->belongsToMany(Category::class, 'category_partner')->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot(['activation_code', 'promo_code', 'number_of_days', 'is_used', 'status_id']);
    }

    /**
     * Get all partner activation codes
     */
    public function allActivationCodes()
    {
        $categories = $this->categories;

        $activationCodes = [];

        foreach ($categories as $category) {
            // Check the activation codes and their status in the pivot relationship
            if ($category->pivot->activation_code) {
                $activationCodes[] = [
                    'activation_code' => $category->pivot->activation_code,
                    'is_used' => $category->pivot->is_used
                ];
            }
        }

        return $activationCodes;
    }

    /**
     * Get all partner activation codes by "is_used"
     */
    public function allActivationCodesByIsUsed($is_used)
    {
        $activationCodes = $this->categories()->wherePivot('is_used', $is_used)
                                ->pluck('pivot.activation_code'); // Retrieve activation codes only

        return $activationCodes;
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
