<?php

namespace App\Models;

use App\Casts\Currency;
use App\Enums\BillingCycle;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'start_date',
        'end_date',
        'price',
        'billing_cycle',
        'auto_renew',
        'note',
        'new_price',
        'new_price_date',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'billing_cycle' => BillingCycle::class,
        'end_date' => 'date',
        'start_date' => 'date',
        'price' => Currency::class,
        'new_price' => Currency::class,
        'new_price_date' => 'date',
    ];

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<Subscription, $this> */
    public function renewedSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'renewed_subscription_id');
    }

    /**
     * Determine whether a price increase takes effect on or before the next renewal.
     */
    public function hasPriceIncreaseAtNextRenewal(): bool
    {
        if ($this->new_price === null || $this->new_price_date === null || $this->end_date === null) {
            return false;
        }

        return $this->new_price_date->lessThanOrEqualTo($this->end_date);
    }

    /** @param  Builder<Subscription>  $query */
    #[Scope]
    protected function autoRenew(Builder $query): void
    {
        $query->where('auto_renew', true);
    }
}
