<?php

namespace OffbeatWP\Exceptions;

use Throwable;

class OffbeatModelNotFoundException extends OffbeatException
{
    /** @inheritDoc */
    public function __construct($message = '', $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}