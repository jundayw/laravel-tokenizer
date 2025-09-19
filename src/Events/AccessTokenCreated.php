<?php

namespace Jundayw\Tokenizer\Events;

use Illuminate\Contracts\Config\Repository;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class AccessTokenCreated extends AccessTokenEvent
{
    public function __construct(
        protected Repository $config,
        Authorizable $authorizable,
        Tokenizable $tokenizable,
        Tokenable $tokenable,
    ) {
        parent::__construct($authorizable, $tokenizable, $tokenable);
    }

    public function getConfig(string $key = null, $default = null): mixed
    {
        if (is_null($key)) {
            return $this->config;
        }
        return $this->config->get($key, $default);
    }
}
