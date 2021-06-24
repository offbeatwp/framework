<?php
namespace OffbeatWP\Form\Fields;

class Terms extends AbstractField {
    const FIELD_TYPE = 'terms';

    public function fromTaxonomy($taxonomy = []): Terms
    {
        $this->setAttribute('taxonomy', $taxonomy);

        return $this;
    }
}