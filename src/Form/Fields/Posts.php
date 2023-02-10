<?php
namespace OffbeatWP\Form\Fields;

class Posts extends AbstractField {
    public const FIELD_TYPE = 'posts';

    /**
     * @param string|string[] $postTypes
     * @return $this
     */
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