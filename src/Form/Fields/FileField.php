<?php
namespace OffbeatWP\Form\Fields;

final class FileField extends AbstractField
{
    public const FIELD_TYPE = 'file';

    /** @return $this */
    public function allowedFileTypes(string $allowedFileTypes)
    {
        $this->setAttribute('allowed_file_types', $allowedFileTypes);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}