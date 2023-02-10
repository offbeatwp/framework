<?php

namespace OffbeatWP\Form\Fields;

abstract class AbstractOptionsField extends AbstractField
{
    /** @var scalar[]|callable */
    protected $options = [];

    /**
     * @deprecated Misleading name as this method actually REPLACES all options
     * @param scalar[]|callable $options
     */
    public function addOptions($options = []) {
        $this->options = $options;
        return $this;
    }

    /** @param scalar[] $options */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @deprecated Use setOption instead
     * @param scalar $value
     */
    public function addOption(string $key, $value): self
    {
        return $this->setOption($key, $value);
    }

    /** @param scalar $value */
    public function setOption(string $key, $value): self {
        $this->options[$key] = $value;
        return $this;
    }

    /** @return scalar[] */
    public function getOptions()
    {
        if (is_callable($this->options)) {
            return call_user_func($this->options);
        }

        return $this->options;
    }
}
