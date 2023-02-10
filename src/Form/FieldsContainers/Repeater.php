<?php
namespace OffbeatWP\Form\FieldsContainers;

final class Repeater extends AbstractFormContainer
{
    public const TYPE = 'repeater';
    public const LEVEL = 30;

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getLevel(): int
    {
        return self::LEVEL;
    }
}