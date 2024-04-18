<?php

namespace OffbeatWP\Layout;

interface ContextInterface
{
    public function getCacheId(): string;
    public function initContext(): void;
}