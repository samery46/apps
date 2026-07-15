<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = auth()->user();
        $plantId = $request->route('plant_id');

        if (!$user->plants->contains($plantId)) {
            return response()->json(['message' => 'Mohon maaf Anda tidak dapat akses'], 403);
        }

        return $next($request);
    }
}
