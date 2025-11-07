<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    public static function getValue(string $key, $default = null, $tenantId = null)
    {
        $cacheKey = "setting_{$tenantId}_{$key}";
        return Cache::rememberForever($cacheKey, function () use ($key, $default, $tenantId) {
            $query = self::query()->where('key', $key);
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            } else {
                $query->whereNull('tenant_id');
            }
            return optional($query->first())->value ?? $default;
        });
    }

    public static function setValue(string $key, $value, $group = null, $tenantId = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => $value]
        );

        Cache::forget("setting_{$tenantId}_{$key}");
        return $setting;
    }
}
