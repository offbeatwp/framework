<?php

namespace OffbeatWP\Components;

final class ComponentSettings
{
    private $defaultValues;

    public function __construct(object $args, array $defaultValues = [])
    {
        foreach (get_object_vars($args) as $key => $value) {
            $this->$key = $value;
        }

        $this->defaultValues = $defaultValues;
    }

    /**
     * Returns the value of the component setting or the default value of the setting if it does not exist.
     * @param non-empty-string $index
     * @return mixed
     */
    public function get(string $index)
    {
        if (property_exists($this, $index)) {
            return $this->$index;
        }

        return $this->defaultValues[$index] ?? null;
    }
}