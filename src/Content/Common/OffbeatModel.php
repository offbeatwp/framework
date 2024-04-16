<?php

namespace OffbeatWP\Content\Common;

abstract class OffbeatModel
{
    /** @return positive-int */
    abstract public function getId(): int;
}