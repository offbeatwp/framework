<?php

namespace OffbeatWP\Form\Shared;

use OffbeatWP\Form\Fields\AbstractField;

trait ConditionalLogicTrait
{
    abstract public function attribute(string $key, $value): AbstractField;

    /** @param string[][][] $conditions */
    public function conditionalLogic(array $conditions): AbstractField
    {
        $this->attribute('conditional_logic', $conditions);
        return $this;
    }
}