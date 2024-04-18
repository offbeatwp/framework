<?php
namespace OffbeatWP\Form\Fields;

final class PostsField extends AbstractField
{
    public const FIELD_TYPE = 'posts';

    /**
     * @param string[] $postTypes
     * @return $this
     */
    public function fromPostTypes(array $postTypes = [])
    {
        $this->setAttribute('post_types', $postTypes);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}