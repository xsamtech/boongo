<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Payment extends Model
{
    use HasFactory;

    public const STATUS_SUCCESSFUL = 0;

    public const STATUS_FAILED = 1;

    public const STATUS_PENDING = 2;

    protected $table = 'payments';

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
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'coins_credited_at' => 'datetime:Y-m-d H:i:s',
        'premium_activated_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * ONE-TO-MANY
     * One type for several payments
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several payments
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several payments
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
