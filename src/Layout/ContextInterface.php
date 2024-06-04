<?php

namespace OffbeatWP\Layout;

interface ContextInterface
{
    /** @return scalar */
    public function getCacheId();
    /** @return void */
    public function initContext();
}