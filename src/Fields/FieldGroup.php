<?php
namespace OffbeatWP\Fields;

class FieldGroup {
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function get()
    {
        return $this->fields;
    }
}