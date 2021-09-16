<?php
namespace OffbeatWP\Form\Fields;

class Term extends AbstractField {
    public const FIELD_TYPE = 'term';

    public function fromTaxonomies($taxonomies = []): Term
    {
        $this->setAttribute('taxonomies', $taxonomies);

        return $this;
    }
}