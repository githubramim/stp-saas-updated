<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Resolves which branch of the user's institute is currently active in the
 * session (the top-bar branch switcher) and provides safe fallbacks so every
 * institute always has at least one branch ("Branch 1" is auto-seeded).
 */
class BranchContext
{
    public static function sessionKey(): string
    {
        return 'current_branch_id';
    }

    /**
     * The branch id bound by SetBranchScope middleware for this request,
     * or 0 when no branch scope is active (public/console context).
     */
    public static function scopeId(): int
    {
        return app()->bound('branch.scope.id')
            ? (int) app('branch.scope.id')
            : 0;
    }

    /**
     * All branches of the user's institute.
     */
    public static function branches(User $user): Collection
    {
        if (! $user->institute_id) {
            return collect();
        }

        return Branch::where('institute_id', $user->institute_id)
            ->orderBy('id')
            ->get();
    }

    /**
     * The branch selected in the top bar, falling back to the user's own
     * branch and then to the institute's first branch.
     */
    public static function current(User $user): ?Branch
    {
        if (! $user->institute_id) {
            return null;
        }

        $selectedId = (int) Session::get(self::sessionKey());

        if ($selectedId > 0) {
            $branch = Branch::where('institute_id', $user->institute_id)
                ->find($selectedId);

            if ($branch) {
                return $branch;
            }
        }

        if ($user->branch_id) {
            $branch = Branch::where('institute_id', $user->institute_id)
                ->find($user->branch_id);

            if ($branch) {
                return $branch;
            }
        }

        return self::defaultFor($user->institute_id);
    }

    /**
     * First branch of the institute — guaranteed to exist by the migration
     * (institutes without branches are auto-given "Branch 1").
     */
    public static function defaultFor(int $instituteId): ?Branch
    {
        return Branch::where('institute_id', $instituteId)
            ->orderBy('id')
            ->first();
    }

    /**
     * Persist the branch choice; only accepts a branch of the user's own
     * institute.
     */
    public static function set(User $user, int $branchId): bool
    {
        if (! $user->institute_id) {
            return false;
        }

        $branch = Branch::where('institute_id', $user->institute_id)
            ->find($branchId);

        if (! $branch) {
            return false;
        }

        Session::put(self::sessionKey(), $branch->id);

        return true;
    }
}
