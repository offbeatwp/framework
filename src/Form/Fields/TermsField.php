<?php
namespace OffbeatWP\Form\Fields;

final class TermsField extends AbstractField
{
    public const FIELD_TYPE = 'terms';

    /**
     * @param string|string[] $taxonomy
     * @return $this
     */
    public function fromTaxonomy(string|array $taxonomy = [])
    {
        $this->setAttribute('taxonomy', $taxonomy);
        return $this;
    }

    /** @return $this */
    public function multiSelect()
    {
        $this->setAttribute('field_type', 'multi_select');
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}