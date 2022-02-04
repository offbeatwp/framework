<?php

namespace OffbeatWP\Content\Enqueue;

class EnqueueScriptBuilder extends AbstractEnqueueBuilder
{
    protected $bindingsToPass = [];
    protected $inFooter = false;

    /**
     * Pass an array of data to the enqueued script.
     * @param string $objectName Script name.
     * @param array $objectValues Array of values.
     * @return static
     */
    public function addBinding(string $objectName, array $objectValues)
    {
        $this->bindingsToPass[$objectName] = $objectValues;
        return $this;
    }

    /**
     * @param bool $value Whether to enqueue the script before BODY instead of in the HEAD.
     * @return static
     */
    public function setInFooter(bool $value = true)
    {
        $this->inFooter = $value;
        return $this;
    }

    public function enqueue(): void
    {
        wp_enqueue_script($this->getHandle(), $this->src, $this->deps, $this->version, $this->inFooter);

        foreach ($this->bindingsToPass as $name => $value) {
            wp_localize_script($this->getHandle(), $name, $value);
        }
    }

    public function register(): void
    {
        wp_register_script($this->getHandle(), $this->src, $this->deps, $this->version, $this->inFooter);

        foreach ($this->bindingsToPass as $objectName => $objectValues) {
            wp_localize_script($this->getHandle(), $objectName, $objectValues);
        }
    }
}