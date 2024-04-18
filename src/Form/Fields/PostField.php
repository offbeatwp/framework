<?php
namespace OffbeatWP\Form\Fields;

final class PostField extends AbstractField {
    public const FIELD_TYPE = 'post';

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