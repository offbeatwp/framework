<?php

namespace OffbeatWP\Layout;

interface ContextInterface
{
    public function getCacheId();
    public function initContext();
}