<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Work extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several sessions for several works
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class)->withTimestamps()->withPivot(['read']);
    }

    /**
     * MANY-TO-MANY
     * Several categories for several works
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * ONE-TO-MANY
     * One type for several works
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several works
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several notifications for a work
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for a work
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
