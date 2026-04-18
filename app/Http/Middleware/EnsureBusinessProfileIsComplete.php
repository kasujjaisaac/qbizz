<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessProfileIsComplete
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $profile = $request->user()?->businessProfile;

        if (! $profile || ! $profile->isComplete()) {
            return redirect()
                ->route('business-profile.edit')
                ->with('status', 'Complete your business profile before you continue to the dashboard and invoices.');
        }

        return $next($request);
    }
}
