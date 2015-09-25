<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Database\Eloquent\Model;

class RequestLogEntry extends Model
{
    protected function updateTimestamps()
    {
        $time = $this->freshTimestamp();

        if (! $this->exists && ! $this->isDirty(static::CREATED_AT)) {
            $this->setCreatedAt($time);
        }
    }
}
