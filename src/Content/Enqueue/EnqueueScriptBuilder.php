<?php

namespace OffbeatWP\Content\Enqueue;

class EnqueueScriptBuilder extends AbstractEnqueueBuilder
{
    protected $bindingsToPass = [];
    protected $inFooter = false;

    /** Pass an array of data to the enqueued javascript */
    public function addBinding(string $name, array $values)
    {
        $this->bindingsToPass[$name] = $values;
        return $this;
    }

    /** Whether to enqueue the script before instead of in the footer */
    public function setInFoorter(bool $value = true)
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
}