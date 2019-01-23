<?php
namespace OffbeatWP\Form\Fields;

class Posts extends AbstractField {
    const FIELD_TYPE = 'posts';

    public function postTypes($postTypes = []) {
        $this->setAttribute('post_types', $postTypes);

        return $this;
    }
}