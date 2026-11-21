<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Branch data isolation (BRD module #21).
 *
 * Registers a global scope named "branch" that restricts every query of the
 * model to the branch selected in the top-bar switcher. The scope is only
 * active while the branch-scope middleware has bound "branch.scope.id" to the
 * container — unauthenticated traffic (public apply, portal, QR scans) and
 * console commands run without the scope and behave as before.
 *
 * Models that own a `branch_id` column are filtered directly. Models that
 * inherit their branch from a parent define `branchRelations()` as an array of
 * [relation path, qualified branch column] pairs, for example:
 *
 *     protected function branchRelations(): array
 *     {
 *         return [
 *             ['student', 'students.branch_id'],
 *             ['project', 'projects.branch_id'],
 *         ];
 *     }
 *
 * All paths are OR-ed together inside one grouped WHERE so a row is visible
 * only when at least one of its branch-bearing parents matches the selection.
 *
 * Bypass it anywhere with: Model::withoutGlobalScope('branch')
 */
trait BelongsToBranch
{
    /** @var array<string, bool> */
    private static array $branchColumnCache = [];

    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $branchId = app()->bound('branch.scope.id')
                ? (int) app('branch.scope.id')
                : 0;

            if ($branchId <= 0) {
                return;
            }

            $model = $builder->getModel();
            $table = $model->getTable();

            if (! array_key_exists($table, self::$branchColumnCache)) {
                self::$branchColumnCache[$table] = Schema::hasColumn($table, 'branch_id');
            }

            if (self::$branchColumnCache[$table]) {
                $builder->where($model->qualifyColumn('branch_id'), $branchId);

                return;
            }

            $relations = method_exists($model, 'branchRelations')
                ? $model->branchRelations()
                : [];

            if ($relations === []) {
                return;
            }

            $builder->where(function (Builder $query) use ($relations, $branchId) {
                foreach ($relations as $index => [$path, $column]) {
                    $callback = fn (Builder $related) => $related->where($column, $branchId);

                    if ($index === 0) {
                        $query->whereHas($path, $callback);
                    } else {
                        $query->orWhereHas($path, $callback);
                    }
                }
            });
        });
    }
}
