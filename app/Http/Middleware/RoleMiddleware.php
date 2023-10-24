<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

class RoleMiddleware extends Controller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $role, $permission = null)
    {
        $user = $this->getCurrentLoggedIn();

        if(!$user) {
            abort(401);
        }
        $role_name = Role::where('id', $user->role_id)->first()->name;

        if($role_name != $role) {
            abort(401);
        }

        return $next($request);
    }
}
