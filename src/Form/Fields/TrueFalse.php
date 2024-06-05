<?php
namespace OffbeatWP\Form\Fields;

class TrueFalse extends AbstractField {
    public const FIELD_TYPE = 'true_false';

    /** @return $this */
    public function stylisedUI(bool $useStylisedUI = true)
    {
        $this->setAttribute('ui', (int)$useStylisedUI);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}