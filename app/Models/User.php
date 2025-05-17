<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_connection' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * Accessor for Age.
     */
    public function age(): int
    {
        return Carbon::parse($this->attributes['birthdate'])->age;
    }

    /**
     * MANY-TO-MANY
     * Several roles for several users
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * MANY-TO-MANY
     * Several organizations for several users
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withTimestamps();
    }

    /**
     * MANY-TO-MANY
     * Several events for several users
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)->withTimestamps()->withPivot(['is_speaker', 'status_id']);
    }

    /**
     * MANY-TO-MANY
     * Several circles for several users
     */
    public function circles(): BelongsToMany
    {
        return $this->belongsToMany(Circle::class)->withTimestamps()->withPivot(['is_admin', 'status_id']);
    }

    /**
     * MANY-TO-MANY
     * Several subscriptions for several users
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class)->withTimestamps()->withPivot(['payment_id', 'status_id']);
    }

    /**
     * ONE-TO-MANY
     * One country for several users
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * ONE-TO-MANY
     * One currency for several users
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several users
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several organizations_owned for a user
     */
    public function organizations_owned(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * MANY-TO-ONE
     * Several toxic_contents for a user
     */
    public function toxic_contents(): HasMany
    {
        return $this->hasMany(ToxicContent::class);
    }

    /**
     * MANY-TO-ONE
     * Several carts for a user
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * MANY-TO-ONE
     * Several likes for a user
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * MANY-TO-ONE
     * Several payments for a user
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * MANY-TO-ONE
     * Several notifications_from for a user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications_from(): HasMany
    {
        return $this->hasMany(Notification::class, 'from_user_id');
    }

    /**
     * MANY-TO-ONE
     * Several notifications_to for a user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications_to(): HasMany
    {
        return $this->hasMany(Notification::class, 'to_user_id');
    }

    /**
     * MANY-TO-ONE
     * Several sessions for a user
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
