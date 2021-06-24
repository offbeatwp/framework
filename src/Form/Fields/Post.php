<?php

namespace OffbeatWP\Form\Fields;

class Post extends AbstractField
{
    const FIELD_TYPE = 'post';

    public function fromPostTypes($postTypes = []): Post
    {
        $this->setAttribute('post_types', $postTypes);

        return $this;
    }
}