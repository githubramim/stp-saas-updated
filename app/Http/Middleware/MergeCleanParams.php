<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clean URLs (no `?param=value` anywhere).
 *
 * Filter/selection values are stored in the session by UiStateController and
 * merged back into the current request here, so controllers can keep reading
 * them with $request->query() / input() exactly as before. Pagination links,
 * form old input and validation all keep working.
 */
class MergeCleanParams
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName) {
            $params = session()->get('clean_params.'.$routeName, []);

            if (is_array($params) && $params !== []) {
                // Merge without overwriting real request data — session values
                // are only a stand-in for the removed query string.
                $request->query->add($params);
                $request->request->add($params);
            }
        }

        return $next($request);
    }
}
