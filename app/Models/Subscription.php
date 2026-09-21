<?php

namespace App\Models;

use App\Casts\Currency;
use App\Enums\BillingCycle;
use Database\Factories\SubscriptionFactory;
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
}
