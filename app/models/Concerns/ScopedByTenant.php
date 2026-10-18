<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait ScopedByTenant
{
    protected static function bootScopedByTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = request()->attributes->get('tenant_id');

            if ($tenantId !== null) {
                $builder->where('tenant_id', $tenantId);
            }
        });
    }
}
