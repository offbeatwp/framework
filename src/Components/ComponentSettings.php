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
     * Returns the value of the component setting or the default value of the setting if it does not exist.
     * @param non-empty-string $index The index of the value to retrieve.
     * @param bool $getUrlParam When true, get the value from a URL param if it exists. Defaults to <i>false</i>.
     * @return mixed
     */
    public function get(string $index, bool $getUrlParam = false)
    {
        if ($getUrlParam && isset($_GET[$index])) {
            return $_GET[$index];
        }

        if (property_exists($this, $index)) {
            return $this->$index;
        }

        return $this->defaultValues[$index] ?? null;
    }
}