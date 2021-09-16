<?php
namespace OffbeatWP\Form\Fields;

class Term extends AbstractInputField {
    public const FIELD_TYPE = 'term';

    public function fromTaxonomies($taxonomies = []): Term
    {
        $this->setAttribute('taxonomies', $taxonomies);

        return $this;
    }
}