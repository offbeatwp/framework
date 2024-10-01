<?php

namespace OffbeatWP\Content\Meta;

final class TermMetaBuilder extends MetaBuilder
{
    public function doRegister(): bool
    {
        return register_term_meta($this->subType, $this->metaKey, $this->args);
    }

    protected static function getObjectType(): string
    {
        return 'term';
    }
}