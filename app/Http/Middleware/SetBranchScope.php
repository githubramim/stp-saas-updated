<?php

namespace App\Http\Middleware;

use App\Support\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Branch data isolation (BRD module #21).
 *
 * Binds the branch selected in the top-bar switcher to the container so the
 * "branch" global scope on Student/Transaction filters every query. Requests
 * without an authenticated institute user (public apply, portal, QR scans,
 * login) bind nothing, so no scope is applied.
 */
class SetBranchScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $branch = BranchContext::current(Auth::user());

            if ($branch) {
                app()->instance('branch.scope.id', $branch->id);
            }
        }

        return $next($request);
    }
}
