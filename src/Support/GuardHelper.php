<?php

namespace Jundayw\Tokenizer\Support;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Jundayw\Tokenizer\Contracts\Auth\SupportsTokenAuth;

trait GuardHelper
{
    /**
     * Attempt to get the guard against the local cache.
     *
     * @param string|null $name
     *
     * @return Guard|SupportsTokenAuth
     */
    public function guard(string|null $name = null): Guard|SupportsTokenAuth
    {
        return Auth::guard($name);
    }
}
