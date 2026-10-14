<?php

namespace App\Models;

use App\Enums\AlertSeverity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Alert extends Model
{
    protected $fillable = [
        'severity', 'title_bn', 'title_en', 'body_bn', 'body_en', 'is_national', 'starts_at', 'ends_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'severity' => AlertSeverity::class,
            'is_national' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Alerts that are live right now. */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('starts_at', '<=', $now)
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    /** National alerts plus those targeted at the given district. */
    public function scopeForDistrict(Builder $query, ?int $districtId): Builder
    {
        return $query->where(function (Builder $q) use ($districtId) {
            $q->where('is_national', true);
            if ($districtId) {
                $q->orWhereHas('districts', fn (Builder $d) => $d->where('districts.id', $districtId));
            }
        });
    }
}
