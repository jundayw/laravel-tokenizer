<?php

namespace Jundayw\Tokenizer\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Jundayw\Tokenizer\Exceptions\AuthenticationException;
use Jundayw\Tokenizer\Exceptions\MissingScopeException;

class CheckScopes
{
    /**
     * Handle the incoming request.
     *
     * @param Request  $request
     * @param Closure  $next
     * @param string[] $scopes
     *
     * @return Response
     * @throws AuthenticationException
     * @throws MissingScopeException
     */
    public function handle(Request $request, Closure $next, string ...$scopes): Response
    {
        if (!$request->user() || !$request->user()->token()) {
            throw new AuthenticationException;
        }

        foreach ($scopes as $scope) {
            if (!$request->user()->tokenCan($scope)) {
                throw new MissingScopeException($scope);
            }
        }

        return $next($request);
    }
}
