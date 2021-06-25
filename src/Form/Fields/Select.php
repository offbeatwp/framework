<?php
namespace OffbeatWP\Form\Fields;

class Select extends FieldWithOptions
{
    const FIELD_TYPE = 'select';

    /**
     * @param string[] $options
     * @return $this
     */
    public function addOptions($options = []): FieldWithOptions
    {
        $this->options = array_replace($this->options, $options);

        return $this;
    }
}