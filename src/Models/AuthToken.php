<?php

namespace Jundayw\Tokenizer\Models;

use Illuminate\Database\Eloquent\Model;
use Jundayw\Tokenizer\Contracts\TokenModel;
use Jundayw\Tokenizer\HasApiTokens;

class AuthToken extends Model implements TokenModel
{
    use HasApiTokens;
}
