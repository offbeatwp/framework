<?php

namespace OffbeatWP\Content\Enqueue;

use InvalidArgumentException;

/** This class requires Wordpress 4.5 or higher. */
final class WpScriptEnqueueBuilder extends AbstractEnqueueBuilder
{
    /** @var array{value: string, inFooter: bool, position: string}[] */
    protected array $bindingsToPass = [];
    /** @var mixed[] */
    protected array $l10nData = [];
    protected string $l10nName = '';
    protected bool $inFooter = false;
    /** @var mixed[] */
    protected static array $vars = [];

    /**
     * Pass a variable to the enqueued script. This variable will be globally available.
     * The actual output of the JavaScript Script tag containing your variable occurs at the time that the enqueued script is printed.
     * @param string $varName Must be alphanumeric.
     * @param scalar|mixed[]|object|null $varValue Will be encoded with json_encode.
     * @param bool $includeAfter When true, the variable will included after the script.
     * @return $this
     */
    public function addVariable(string $varName, $varValue, bool $includeAfter = false)
    {
        if (!ctype_alnum($varName)) {
            throw new InvalidArgumentException('AddBinding requires a alphanumeric variable name.');
        }

        $this->bindingsToPass[$varName] = [
            'value' => json_encode($varValue, is_object($varValue) ? JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT : JSON_THROW_ON_ERROR),
            'position' => ($includeAfter) ? 'after' : 'before',
        ];

        return $this;
    }

    /**
     * Localize a script.<br>
     * Works only if the script has already been registered.
     * @param mixed[] $l10n
     * @return $this
     */
    public function localize(array $l10n, string $objectName = 'tl')
    {
        $this->l10nData = $l10n;
        $this->l10nName = $objectName;
        return $this;
    }

    /**
     * Whether to enqueue the script before BODY instead of in the HEAD.
     * @return $this
     */
    public function setInFooter(bool $value = true)
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
