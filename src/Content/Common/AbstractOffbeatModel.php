<?php

namespace OffbeatWP\Content\Common;

abstract class AbstractOffbeatModel
{
    protected ?array $metaData = null;
    protected array $metaInput = [];
    protected array $metaToUnset = [];

    abstract public function getId(): ?int;
    abstract public function getMetaData(): array;

    /** @return array An array of all values whose key is not prefixed with <i>_</i> */
    public function getMetaValues(): array
    {
        $values = [];

        foreach ($this->getMetaData() as $key => $value) {
            if ($key[0] !== '_') {
                $values[$key] = reset($value);
            }
        }

        return $values;
    }
}