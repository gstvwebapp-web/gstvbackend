<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Module;

class CheckUserPermission
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $ids = explode(',', $user->moduleID);
        $modules = Module::where('status', 'Active')->whereIn('id', $ids)->pluck('moduleLink')->toArray();
        $checkurl = explode('/',$request->path());
        // Implement your permission check logic
        array_push($modules,'user');
        if (!in_array($checkurl[0],$modules)) {
            abort(403, 'Unauthorized: You do not have permission to access this page.');
        }
        return $next($request);
    }
}
