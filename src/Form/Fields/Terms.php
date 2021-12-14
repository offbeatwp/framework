<?php
namespace OffbeatWP\Form\Fields;

class Terms extends AbstractField {
    public const FIELD_TYPE = 'terms';

    public function fromTaxonomy($taxonomy = []) {
        $this->setAttribute('taxonomy', $taxonomy);

        return $this;
    }
}