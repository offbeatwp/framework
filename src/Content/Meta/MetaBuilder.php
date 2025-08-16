<?php

namespace OffbeatWP\Content\Meta;

use TypeError;

abstract class MetaBuilder
{
    protected string $metaKey;
    protected string $subType;
    protected array $args = [];
    protected $validationCallback = null;

    public function __constructor(string $metaKey, string $subType): void
    {
        $this->metaKey = $metaKey;
        $this->subType = $subType;
    }

    public function register(): bool
    {
        if (!$this->areDefaultValueAndTypeCompatible()) {
            throw new TypeError('Type/default mismatch. The type was defined as "' . $this->args['type'] . '" but the default value is of type "' . gettype($this->args['default']) . '".');
        }

        return $this->doRegister();
    }

    abstract protected function doRegister(): bool;

    abstract protected static function getObjectType(): string;

    /**
     * The type of data associated with this meta key.
     * @param string $type 'string', 'boolean', 'integer', 'number', 'array', 'object'
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->args['type'] = $type;
        return $this;
    }

    /**
     * A description of the data attached to this meta key.
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->args['description'] = $description;
        return $this;
    }

    /**
     * Whether the meta key has one value per object, or an array of values per object.
     * @param bool $single
     * @return $this
     */
    public function setSingle(bool $single): self
    {
        $this->args['single'] = $single;
        return $this;
    }

    /**
     * The default value returned if no value has been set yet.<br>
     * When using a non-single meta key, the default value is for the first entry.<br>
     * In other words, when calling single set to false, the default value given here will be wrapped in an array.
     * @param mixed $defaultValue
     * @return $this
     */
    public function setDefault($defaultValue): self
    {
        $this->args['default'] = $defaultValue;
        return $this;
    }

    /**
     * A function or method to call when sanitizing meta key data.
     * @param callable $sanitizeCallback
     * @return $this
     */
    public function setSanitizeCallback(callable $sanitizeCallback): self
    {
        $this->args['sanitize_callback'] = $sanitizeCallback;
        return $this;
    }

    /**
     * A function or method to call when performing edit_post_meta, add_post_meta, and delete_post_meta capability checks.
     * @param callable $authCallback
     * @return $this
     */
    public function setAuthCallback(callable $authCallback): self
    {
        $this->args['auth_callback'] = $authCallback;
        return $this;
    }

    /**
     * Whether data associated with this meta key can be considered public and should be accessible via the REST API.<br>
     * A custom post type must also declare support for custom fields for registered meta to be accessible via REST.
     * @param bool|array{schema: array, prepare_callback: callable} $showInRest
     * @return $this
     */
    public function setShowInRest($showInRest): self
    {
        if (!is_bool($showInRest) && !is_array($showInRest)) {
            throw new TypeError('The setShowInRest method requires an array or boolean as argument.');
        }

        $this->args['show_in_rest'] = $showInRest;
        return $this;
    }

    /**
     * Define a callback to validate the meta before setting it.<br>
     * This callback receives the metakey and value as arguments and should return a MetaChangeResult.
     * @param callable $validationCallback
     * @return $this
     */
    public function setValidationCallback(callable $validationCallback): self
    {
        $this->validationCallback = $validationCallback;
        return $this;
    }

    public function getValidationCallback(): ?callable
    {
        return $this->validationCallback;
    }

    private function areDefaultValueAndTypeCompatible(): bool
    {
        if (isset($this->args['default']) && $this->args['default'] !== null && isset($this->args['type'])) {
            $defaultType = gettype($this->args['default']);

            // Number type needs special handling because PHP and WordPress both use different naming schemes and NEITHER ARE CALLED FLOAT.
            if ($this->args['type'] === 'number') {
                return ($defaultType === 'integer' || $defaultType === 'double');
            }

            return $this->args['type'] === $defaultType;
        }

        return true;
    }
}