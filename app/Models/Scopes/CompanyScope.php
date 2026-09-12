<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Flag to prevent infinite recursion during auth resolution.
     */
    protected static bool $isResolving = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (static::$isResolving) {
            return;
        }

        static::$isResolving = true;

        try {
            if (auth()->check()) {
                $user = auth()->user();

                if ($user && ! $user->isSuperAdmin()) {
                    $builder->where($model->getTable().'.company_id', $user->company_id);
                }
            }
        } finally {
            static::$isResolving = false;
        }
    }
}
