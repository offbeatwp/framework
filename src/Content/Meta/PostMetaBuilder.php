<?php

namespace OffbeatWP\Content\Meta;

final class PostMetaBuilder extends MetaBuilder
{
    public function doRegister(): bool
    {
        return register_post_meta($this->subType, $this->metaKey, $this->args);
    }

    protected static function getObjectType(): string
    {
        return 'post';
    }
}