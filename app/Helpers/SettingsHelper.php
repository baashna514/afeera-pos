<?php

use App\Services\CompanySettingService;

if (! function_exists('company_setting')) {
    /**
     * Get or set a company setting.
     */
    function company_setting(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        return CompanySettingService::get($key, $default, $companyId);
    }
}

if (! function_exists('setting')) {
    /**
     * Alias for company_setting.
     */
    function setting(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        return CompanySettingService::get($key, $default, $companyId);
    }
}

if (! function_exists('company_has_feature')) {
    /**
     * Check if a feature is enabled for the company.
     */
    function company_has_feature(string $feature, ?int $companyId = null): bool
    {
        return CompanySettingService::hasFeature($feature, $companyId);
    }
}

if (! function_exists('has_feature')) {
    /**
     * Alias for company_has_feature.
     */
    function has_feature(string $feature, ?int $companyId = null): bool
    {
        return CompanySettingService::hasFeature($feature, $companyId);
    }
}
