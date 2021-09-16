<?php
namespace OffbeatWP\Form\Fields;

class Posts extends AbstractInputField {
    public const FIELD_TYPE = 'posts';

    public function fromPostTypes($postTypes = []): Posts
    {
        $this->setAttribute('post_types', $postTypes);

        return $this;
    }
}