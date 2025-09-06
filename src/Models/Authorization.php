<?php

namespace Jundayw\Tokenizer\Models;

use Illuminate\Database\Eloquent\Model;
use Jundayw\Tokenizer\Contracts\Authorizable;

class Authorization extends Model implements Authorizable
{
    use HasAuthorizable;
}
