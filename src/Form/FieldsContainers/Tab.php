<?php
namespace OffbeatWP\Form\FieldsContainers;

class Tab extends AbstractFieldsContainer
{
    public const TYPE = 'tab';
    public const LEVEL = 10;

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getLevel(): int
    {
        return self::LEVEL;
    }
}
