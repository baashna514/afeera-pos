<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;

class CompanySettingService
{
    /**
     * In-memory cache for company settings per request.
     *
     * @var array<int|string, array<string, mixed>>
     */
    protected static array $cache = [];

    /**
     * Resolve the target company ID.
     */
    public static function resolveCompanyId(?int $companyId = null): ?int
    {
        if ($companyId !== null) {
            return $companyId;
        }

        if (Auth::check() && Auth::user()->company_id) {
            return (int) Auth::user()->company_id;
        }

        $defaultCompany = Company::first();

        return $defaultCompany?->id;
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $resolvedCompanyId = static::resolveCompanyId($companyId);
        if (! $resolvedCompanyId) {
            return $default;
        }

        static::loadCompanySettings($resolvedCompanyId);

        if (! array_key_exists($key, static::$cache[$resolvedCompanyId])) {
            return $default;
        }

        $val = static::$cache[$resolvedCompanyId][$key];

        if ($val === null) {
            return $default;
        }

        return static::castValue($val);
    }

    /**
     * Check if a feature is enabled.
     */
    public static function hasFeature(string $feature, ?int $companyId = null): bool
    {
        $val = static::get('features.'.$feature, true, $companyId);

        if (is_bool($val)) {
            return $val;
        }

        if ($val === '1' || $val === 'true' || $val === 1) {
            return true;
        }

        if ($val === '0' || $val === 'false' || $val === 0) {
            return false;
        }

        return (bool) $val;
    }

    /**
     * Set a single setting value.
     */
    public static function set(string $key, mixed $value, ?int $companyId = null): void
    {
        $resolvedCompanyId = static::resolveCompanyId($companyId);
        if (! $resolvedCompanyId) {
            return;
        }

        $serializedValue = is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        CompanySetting::withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $resolvedCompanyId, 'key' => $key],
            ['value' => $serializedValue]
        );

        static::$cache[$resolvedCompanyId][$key] = $serializedValue;
    }

    /**
     * Set multiple settings at once.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function setMany(array $settings, ?int $companyId = null): void
    {
        $resolvedCompanyId = static::resolveCompanyId($companyId);
        if (! $resolvedCompanyId) {
            return;
        }

        foreach ($settings as $key => $value) {
            static::set($key, $value, $resolvedCompanyId);
        }
    }

    /**
     * Get all settings for a company.
     *
     * @return array<string, mixed>
     */
    public static function all(?int $companyId = null): array
    {
        $resolvedCompanyId = static::resolveCompanyId($companyId);
        if (! $resolvedCompanyId) {
            return [];
        }

        static::loadCompanySettings($resolvedCompanyId);

        $result = [];
        foreach (static::$cache[$resolvedCompanyId] as $k => $v) {
            $result[$k] = static::castValue($v);
        }

        return $result;
    }

    /**
     * Flush in-memory cache.
     */
    public static function flushCache(?int $companyId = null): void
    {
        if ($companyId !== null) {
            unset(static::$cache[$companyId]);
        } else {
            static::$cache = [];
        }
    }

    /**
     * Load settings from database into memory cache.
     */
    protected static function loadCompanySettings(int $companyId): void
    {
        if (isset(static::$cache[$companyId])) {
            return;
        }

        static::$cache[$companyId] = CompanySetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Auto-cast setting values (booleans, json, numbers).
     */
    protected static function castValue(mixed $val): mixed
    {
        if ($val === '1') {
            return true;
        }
        if ($val === '0') {
            return false;
        }
        if ($val === 'true') {
            return true;
        }
        if ($val === 'false') {
            return false;
        }

        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $val;
    }
}
