<?php

class RedirectIfNotAdmin
{
/**
 * Handle an incoming request.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Closure  $next
 * @param  string|null  $guard
 * @return mixed
 */
public function handle($request, Closure $next, $guard = 'BusinessUsers')
{
    if (!Auth::guard($guard)->check()) {
        return redirect('/businessuser');
    }

    return $next($request);
}

}

?>