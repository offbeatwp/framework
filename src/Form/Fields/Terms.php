<?php
namespace OffbeatWP\Form\Fields;

class Terms extends AbstractField {
    public const FIELD_TYPE = 'terms';

    /** @param string|string[] $taxonomy */
    public function fromTaxonomy($taxonomy): self
    {
        $this->setAttribute('taxonomy', $taxonomy);
        return $this;
    }

    final public function multiSelect(): self
    {
        $this->setAttribute('field_type', 'multi_select');
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}