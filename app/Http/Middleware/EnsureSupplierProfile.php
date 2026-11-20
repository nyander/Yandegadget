<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suppliers fill in their business details before they can offer items.
 */
class EnsureSupplierProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->supplier === null) {
            return to_route('supplier.profile.edit');
        }

        return $next($request);
    }
}
