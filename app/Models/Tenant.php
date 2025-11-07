<?php

namespace App\Models;

use App\Models\SuperAdmin\Package;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = ['id', 'name', 'domain', 'data'];

    /**
     * Get all of the subscriptions for the tenant (the full history).
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the current, active subscription for the tenant.
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
                    ->whereNull('ends_at')
                    ->where('status', 'active')
                    ->latestOfMany();
    }
    
    /**
     * (Optional) Get the current package directly.
     */
    public function package()
    {
       
        return $this->hasOneThrough(
            Package::class,
            Subscription::class,
            'tenant_id', 
            'id', 
            'id', 
            'package_id' 
        )->whereNull('subscriptions.ends_at');
    }
}
