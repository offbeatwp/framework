<?php
namespace OffbeatWP\Form\Fields;

final class PostsField extends AbstractField
{
    public const FIELD_TYPE = 'posts';

    public function fromPostTypes($postTypes = [])
    {
        $this->setAttribute('post_types', $postTypes);

        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}