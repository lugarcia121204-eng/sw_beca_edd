<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureEvaluatorAuthorized
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->isAuthorizedEvaluator()) {
            return $request->isMethod('GET')
                ? redirect()->route('evaluator.pending')
                : abort(403, 'Tu cuenta requiere autorización administrativa para evaluar.');
        }
        return $next($request);
    }
}
