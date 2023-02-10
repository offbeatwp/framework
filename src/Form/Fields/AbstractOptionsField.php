<?php

namespace OffbeatWP\Form\Fields;

abstract class AbstractOptionsField extends AbstractField
{
    /** @var scalar[]|callable */
    protected $options = [];

    /**
     * @deprecated Misleading name as this method actually REPLACES all options
     * @param scalar[]|callable $options
     * @return $this
     */
    final public function addOptions($options = []) {
        $this->options = $options;
        return $this;
    }

    /** @param scalar[] $options */
    final public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /** @param scalar $value */
    final public function setOption(string $key, $value): self {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @deprecated Use setOption instead.
     * @param string $key
     * @param scalar $value
     * @return $this
     */
    final public function addOption(string $key, $value): self
    {
        return $this->setOption($key, $value);
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
