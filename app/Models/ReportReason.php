<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ReportReason extends Model
{
    use HasFactory;

    protected $table = 'report_reasons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-ONE
     * Several toxic_contents for a report_reason
     */
    public function toxic_contents(): HasMany
    {
        return $this->hasMany(ToxicContent::class);
    }
}
