<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Branch data isolation (BRD module #21).
 *
 * Registers a global scope named "branch" that restricts every query of the
 * model to the branch selected in the top-bar switcher. The scope is only
 * active while the branch-scope middleware has bound "branch.scope.id" to the
 * container — unauthenticated traffic (public apply, portal, QR scans) and
 * console commands run without the scope and behave as before.
 *
 * Bypass it anywhere with: Model::withoutGlobalScope('branch')
 */
trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $branchId = app()->bound('branch.scope.id')
                ? (int) app('branch.scope.id')
                : 0;

            if ($branchId > 0) {
                $builder->where($builder->getModel()->getTable().'.branch_id', $branchId);
            }
        });
    }
}
