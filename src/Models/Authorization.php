<?php

namespace Jundayw\Tokenizer\Models;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Jundayw\Tokenizer\Contracts\Authorizable;

class Authorization extends Model implements Authorizable
{
    use SoftDeletes;
    use HasAuthorizable;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = 'updated_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const DELETED_AT = 'deleted_at';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scopes'                     => 'array',
        'access_token_expire_at'     => 'datetime',
        'refresh_token_available_at' => 'datetime',
        'refresh_token_expire_at'    => 'datetime',
        'last_used_at'               => 'datetime',
        'created_at'                 => 'datetime',
        'updated_at'                 => 'datetime',
        'deleted_at'                 => 'datetime',
    ];

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param DateTimeInterface $date
     *
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date instanceof DateTimeImmutable ?
            CarbonImmutable::instance($date)->toIso8601ZuluString() :
            Carbon::instance($date)->toIso8601ZuluString();
    }
}
