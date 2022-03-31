<?php

namespace OffbeatWP\Components;

final class ComponentSettings
{
    private $defaultValues;

    public function __construct($args, array $defaultValues = [])
    {
        foreach (get_object_vars($args) as $key => $value) {
            $this->$key = $value;
        }

        $this->defaultValues = $defaultValues;
    }

    /**
     * Set a value manually.
     * @param string $index
     * @param mixed $value
     */
    public function set(string $index, $value): void
    {
        $this->$index = $value;
    }

    /**
     * Returns the value of the component setting or the default value of the setting if it does not exist.
     * @param non-empty-string $index The index of the value to retrieve.
     * @return mixed
     */
    public function get(string $index)
    {
        if (property_exists($this, $index)) {
            return $this->$index;
        }

        return $this->defaultValues[$index] ?? null;
    }

    /**
     * Return the value form a url param.
     * If the url param does not exist, the component setting value is returned instead.
     * @param non-empty-string $index
     * @return mixed
     */
    public function getUrlParam(string $index)
    {
        return $_GET[$index] ?? $this->get($index);
    }
}