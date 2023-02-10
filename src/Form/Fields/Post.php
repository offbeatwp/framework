<?php
namespace OffbeatWP\Form\Fields;

class Post extends AbstractField {
    public const FIELD_TYPE = 'post';

    /** @param string|string[] $postTypes */
    public function fromPostTypes($postTypes): self
    {
        $this->setAttribute('post_types', $postTypes);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}