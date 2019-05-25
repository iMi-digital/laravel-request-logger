<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Database\Eloquent\Model;

class RequestLogEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'path',
        'method',
        'agent',
        'get',
        'post',
        'cookies',
        'session'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ip' => 'string',
        'path' => 'string',
        'method' => 'string',
        'agent' => 'string',
        'get' => 'string',
        'post' => 'string',
        'cookies' => 'string',
        'session' => 'string'
    ];

    const UPDATED_AT = null;
}
