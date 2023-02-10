<?php
namespace OffbeatWP\Form\FieldsContainers;

class Section extends AbstractFieldsElementWithParent
{
    public const TYPE = 'section';
    public const LEVEL = 20;

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getLevel(): int
    {
        return self::LEVEL;
    }
}