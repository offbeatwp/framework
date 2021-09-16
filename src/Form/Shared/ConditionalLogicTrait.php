<?php

namespace OffbeatWP\Form\Shared;

use OffbeatWP\Form\Fields\AbstractInputField;

trait ConditionalLogicTrait
{
    abstract public function attribute(string $key, $value): AbstractInputField;

    /** @param string[][][] $conditions */
    public function conditionalLogic(array $conditions): AbstractInputField
    {
        $this->attribute('conditional_logic', $conditions);
        return $this;
    }
}