<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'location_count',
        'user_count',
        'product_count',
        'invoice_count',
        'interval',
        'trial_days',
        'businesses',
        'created_by',
        'is_active',
        'mark_package_as_popular',
        'is_private',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'mark_package_as_popular' => 'boolean',
        'is_private' => 'boolean',
        'price' => 'float',
        'businesses' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // public function subscriptions()
    // {
    //     return $this->hasMany(Subscription::class);
    // }
}
