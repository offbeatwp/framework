<?php

namespace OffbeatWP\Content\Enqueue;

use InvalidArgumentException;

/** This class requires Wordpress 4.5 or higher. */
class EnqueueScriptBuilder extends AbstractEnqueueBuilder
{
    /** @var array{value: string, inFooter: bool} */
    protected $bindingsToPass = [];
    protected $inFooter = false;
    protected static $vars = [];

    /**
     * Pass a variable to the enqueued script. This variable will be globally available.
     * The actual output of the JavaScript Script tag containing your variable occurs at the time that the enqueued script is printed.
     * @param string $varName Must be alphanumeric.
     * @param scalar|array|object|null $varValue Will be encoded with json_encode.
     * @param bool $includeAfter When true, the variable will included after the script.
     * @return static
     */
    public function addVariable(string $varName, $varValue, bool $includeAfter = false)
    {
        if (!ctype_alnum($varName)) {
            throw new InvalidArgumentException('AddBinding requires a alphanumeric variable name.');
        }

        $this->bindingsToPass[$varName] = [
            'value' => json_encode($varValue),
            'position' => ($includeAfter) ? 'after' : 'before',
        ];

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
        if ($this->registered) {
            wp_enqueue_script($this->getHandle());
        } else {
            wp_enqueue_script($this->getHandle(), $this->src, $this->deps, $this->version, $this->inFooter);
        }

        foreach ($this->bindingsToPass as $varName => $args) {
            if (!array_key_exists($varName, self::$vars) || self::$vars[$varName] !== $args['value']) {
                wp_add_inline_script($this->getHandle(), "var {$varName} = {$args['value']};", $args['position']);
                self::$vars[$varName] = $args['value'];
            }
        }
    }

    /** @return static */
    public function register()
    {
        wp_register_script($this->getHandle(), $this->src, $this->deps, $this->version, $this->inFooter);
        $this->registered = true;
        return $this;
    }
}