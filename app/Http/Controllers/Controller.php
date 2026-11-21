<?php

namespace App\Http\Controllers;

use App\Support\BranchContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Guard an implicitly bound model against both the active institute and
     * the branch selected in the top bar.
     *
     * Implicit route-model binding runs before the branch scope middleware, so
     * a scoped model can still be resolved from a guessed URL. Call this in
     * every show/edit/update/destroy action.
     *
     * @param  array<int, string>  $branchPaths  dotted relation paths to follow
     *                                           when the model has no direct
     *                                           branch_id (e.g. 'batch.project')
     */
    protected function assertBranchContext(Model $model, array $branchPaths = []): void
    {
        abort_if(! Auth::check(), 403);

        $instituteId = (int) Auth::user()->institute_id;
        $modelInstituteId = (int) ($model->institute_id ?? 0);

        // Some child models inherit the institute from their parent.
        if ($modelInstituteId === 0) {
            foreach ($branchPaths as $path) {
                $related = $this->relatedAlong($model, $path);
                if ($related && (int) ($related->institute_id ?? 0) > 0) {
                    $modelInstituteId = (int) $related->institute_id;
                    break;
                }
            }
        }

        abort_if($modelInstituteId !== $instituteId, 404);

        $branchId = (int) ($model->branch_id ?? 0);

        if ($branchId === 0) {
            foreach ($branchPaths as $path) {
                $related = $this->relatedAlong($model, $path);
                if ($related && (int) ($related->branch_id ?? 0) > 0) {
                    $branchId = (int) $related->branch_id;
                    break;
                }
            }
        }

        abort_if($branchId !== BranchContext::scopeId(), 404);
    }

    /**
     * Resolve a dotted Eloquent relation path, returning null if any link is
     * missing.
     */
    private function relatedAlong(Model $model, string $path): ?Model
    {
        $related = $model;

        foreach (explode('.', $path) as $relation) {
            $related = $related->{$relation};

            if (! $related instanceof Model) {
                return null;
            }
        }

        return $related;
    }
}
