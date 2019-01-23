<?php
namespace OffbeatWP\Form\Fields;

class Terms extends AbstractField {
    const FIELD_TYPE = 'terms';

    public function taxonomies($taxonomies = []) {
        $this->setAttribute('taxonomies', $taxonomies);

        return $this;
    }
}