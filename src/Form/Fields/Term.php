<?php
namespace OffbeatWP\Form\Fields;

class Term extends AbstractField {
    public const FIELD_TYPE = 'term';

    /** @param string|string[] $taxonomies */
    public function fromTaxonomies($taxonomies): self
    {
        $this->setAttribute('taxonomies', $taxonomies);

        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}