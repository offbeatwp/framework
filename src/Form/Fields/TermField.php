<?php
namespace OffbeatWP\Form\Fields;

final class TermField extends AbstractField
{
    public const FIELD_TYPE = 'term';

    /**
     * @param string[] $taxonomies
     * @return $this
     */
    public function fromTaxonomies(array $taxonomies = []) {
        $this->setAttribute('taxonomies', $taxonomies);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}