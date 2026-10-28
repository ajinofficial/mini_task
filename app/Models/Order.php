<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = array('customer_id', 'subtotal', 'tax', 'total', 'paid_amount');
    protected $appends  = array('balance_due', 'change_due');

    protected function casts(): array
    {
        return array('subtotal' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2', 'paid_amount' => 'decimal:2');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, round((float) $this->total - (float) $this->paid_amount, 2));
    }

    public function getChangeDueAttribute(): float
    {
        return max(0, round((float) $this->paid_amount - (float) $this->total, 2));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
