<?php

namespace OffbeatWP\Content\Enqueue;

use InvalidArgumentException;

/** This class requires Wordpress 4.5 or higher. */
final class WpScriptEnqueueBuilder extends AbstractEnqueueBuilder
{
    /** @var array{value: string, inFooter: bool, position: string}[] */
    protected array $bindingsToPass = [];
    protected array $l10nData = [];
    protected string $l10nName = '';
    protected bool $inFooter = false;
    protected static array $vars = [];

    /**
     * Pass a variable to the enqueued script. This variable will be globally available.
     * The actual output of the JavaScript Script tag containing your variable occurs at the time that the enqueued script is printed.
     * @param string $varName Must be alphanumeric.
     * @param scalar|array|object|null $varValue Will be encoded with json_encode.
     * @param bool $includeAfter When true, the variable will included after the script.
     */
    public function addVariable(string $varName, $varValue, bool $includeAfter = false): self
    {
        if (!ctype_alnum($varName)) {
            throw new InvalidArgumentException('AddBinding requires a alphanumeric variable name.');
        }

        $this->bindingsToPass[$varName] = [
            'value' => json_encode($varValue, JSON_THROW_ON_ERROR),
            'position' => ($includeAfter) ? 'after' : 'before',
        ];

        return $this;
    }

    /**
     * Localize a script.<br>
     * Works only if the script has already been registered.
     */
    public function localize(array $l10n, string $objectName = 'tl'): self
    {
        $this->l10nData = $l10n;
        $this->l10nName = $objectName;
        return $this;
    }

    /** Whether to enqueue the script before BODY instead of in the HEAD. */
    public function setInFooter(bool $value = true): self
    {
        $this->inFooter = $value;
        return $this;
    }

    public function enqueue(string $handle): void
    {
        if ($this->src) {
            wp_enqueue_script($handle, $this->src, $this->deps, $this->version, $this->inFooter);
        } else {
            wp_enqueue_script($handle);
        }

        foreach ($this->bindingsToPass as $varName => $args) {
            if (!array_key_exists($varName, self::$vars) || self::$vars[$varName] !== $args['value']) {
                wp_add_inline_script($handle, "var {$varName} = {$args['value']};", $args['position']);
                self::$vars[$varName] = $args['value'];
            }
        }

        if ($this->l10nName) {
            wp_localize_script($handle, $this->l10nName, $this->l10nData);
        }
    }

    /** Returns a WpScript instance if script was registered successfully or null if it was not. */
    public function register(string $handle): ?WpScriptHolder
    {
        if (wp_register_script($handle, $this->src, $this->deps, $this->version, $this->inFooter)) {
            return new WpScriptHolder($handle);
        }

        return null;
    }
}