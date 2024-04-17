<?php
namespace OffbeatWP\Form\Fields;

final class TrueFalseField extends AbstractField
{
    public const FIELD_TYPE = 'true_false';

    public function stylisedUI(bool $useStylisedUI = true): self
    {
        $this->setAttribute('ui', (int)$useStylisedUI);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}