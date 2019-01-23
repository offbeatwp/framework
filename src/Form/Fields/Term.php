<?php
namespace OffbeatWP\Form\Fields;

class Term extends AbstractField {
    const FIELD_TYPE = 'term';

    public function taxonomies($taxonomies = []) {
        $this->setAttribute('taxonomies', $taxonomies);

        return $this;
    }
}