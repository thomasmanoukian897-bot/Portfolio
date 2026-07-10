<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPostSubscription extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscriber_id',
        'subscribed_to_id',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function subscribedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subscribed_to_id');
    }
}
