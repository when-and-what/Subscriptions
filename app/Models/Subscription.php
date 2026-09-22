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
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'billing_cycle' => BillingCycle::class,
        'end_date' => 'date',
        'start_date' => 'date',
        'price' => Currency::class,
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

    #[Scope]
    protected function autoRenew(Builder $query): void
    {
        $query->where('auto_renew', true);
    }
}
